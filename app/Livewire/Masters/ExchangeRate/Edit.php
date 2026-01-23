<?php

namespace App\Livewire\Masters\ExchangeRate;

use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\Validation\CurrencyValidationHelper;
use App\Models\CMW\Master\ExchangeRate;
use Exception;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Exchange Rate')]
class Edit extends Component
{
    public ?ExchangeRate $exchangeRate = null;

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
        $this->handlePopulateCurrency();
    }

    private function handlePopulateCurrency(): void
    {
        $this->dropdown_currency = PopulateDataHelper::getCurrencies();
    }

    public function rules(): array
    {
        return [
            'inputs.from_currency_id' => CurrencyValidationHelper::currencyIdEditRules($this->exchangeRate?->from_currency_id ?? 1),
            'inputs.to_currency_id' => CurrencyValidationHelper::currencyIdEditRules($this->exchangeRate?->to_currency_id ?? 1),
            'inputs.effective_date' => 'required|date',
            'inputs.rate' => CurrencyValidationHelper::exchangeRateRules(),
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit exchange rate');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                // Update primary rate
                $this->exchangeRate->update([
                    'rate' => $validated['inputs']['rate'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                // Also update reciprocal rate
                $reciprocalRate = $validated['inputs']['rate'] > 0
                    ? round(1 / $validated['inputs']['rate'], 6)
                    : 0;

                $reciprocal = ExchangeRate::where('from_currency_id', $this->exchangeRate->to_currency_id)
                    ->where('to_currency_id', $this->exchangeRate->from_currency_id)
                    ->where('effective_date', $this->exchangeRate->effective_date)
                    ->first();

                if ($reciprocal) {
                    $reciprocal->update([
                        'rate' => $reciprocalRate,
                        'remarks' => $validated['inputs']['remarks'] ? "[Reciprocal] {$validated['inputs']['remarks']}" : '[Reciprocal]',
                        'is_active' => $validated['inputs']['is_active'],
                        'updated_by' => Auth::id(),
                    ]);
                }

                Flux::toast('Exchange rate updated successfully (with reciprocal)', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.exchange-rate.refresh');
                $this->modal('edit-exchange-rate')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating exchange rate', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.exchange-rate.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit exchange rate');
        $this->resetValidation();
        $this->handlePopulateCurrency();

        $this->exchangeRate = ExchangeRate::with(['fromCurrency', 'toCurrency'])->findOrFail($id);

        $this->inputs = [
            'from_currency_id' => $this->exchangeRate->from_currency_id,
            'to_currency_id' => $this->exchangeRate->to_currency_id,
            'effective_date' => $this->exchangeRate->effective_date->format('Y-m-d'),
            'rate' => $this->exchangeRate->rate,
            'remarks' => $this->exchangeRate->remarks,
            'is_active' => $this->exchangeRate->is_active,
        ];

        $this->modal('edit-exchange-rate')->show();
    }

    public function render()
    {
        return view('livewire.masters.exchange-rate.edit');
    }
}
