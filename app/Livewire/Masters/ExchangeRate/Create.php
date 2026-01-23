<?php

namespace App\Livewire\Masters\ExchangeRate;

use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\Validation\CurrencyValidationHelper;
use App\Models\CMW\Master\ExchangeRate;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Exchange Rate')]
class Create extends Component
{
    public $inputs = [
        'from_currency_id' => '',
        'to_currency_id' => '',
        'effective_date' => '',
        'rate' => 1,
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_currency = [];

    public function mount(): void
    {
        $this->authorize('create exchange rate');
        $this->handlePopulateCurrency();
        $this->inputs['effective_date'] = now()->format('Y-m-d');
    }

    private function handlePopulateCurrency(): void
    {
        $this->dropdown_currency = PopulateDataHelper::getCurrencies();
    }

    public function rules(): array
    {
        return [
            'inputs.from_currency_id' => CurrencyValidationHelper::currencyIdRules(),
            'inputs.to_currency_id' => [
                'required',
                'exists:currencies,id,is_active,1',
                'different:inputs.from_currency_id',
            ],
            'inputs.effective_date' => 'required|date|after_or_equal:'.now()->subDays(30)->format('Y-m-d'),
            'inputs.rate' => CurrencyValidationHelper::exchangeRateRules(),
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'inputs.to_currency_id.different' => 'The target currency must be different from the source currency.',
            'inputs.effective_date.after_or_equal' => 'The effective date cannot be more than 30 days in the past.',
        ];
    }

    public function save(): void
    {
        $this->authorize('create exchange rate');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                ExchangeRate::createOrUpdateWithReciprocal(
                    (int) $validated['inputs']['from_currency_id'],
                    (int) $validated['inputs']['to_currency_id'],
                    $validated['inputs']['effective_date'],
                    (float) $validated['inputs']['rate'],
                    $validated['inputs']['remarks'],
                    Auth::id()
                );

                Flux::toast('Exchange rate created successfully (with reciprocal)', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.exchange-rate.refresh');
                $this->modal('create-exchange-rate')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Exchange rate already exists for these currencies and date', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating exchange rate', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.exchange-rate.create.open')]
    public function openModal(): void
    {
        $this->handlePopulateCurrency();
        $this->inputs = [
            'from_currency_id' => '',
            'to_currency_id' => '',
            'effective_date' => now()->format('Y-m-d'),
            'rate' => 1,
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-exchange-rate')->show();
    }

    public function render()
    {
        return view('livewire.masters.exchange-rate.create');
    }
}
