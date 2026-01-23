<?php

namespace App\Livewire\Masters\Currency;

use App\Helpers\CMW\Dictionary\CurrencyDictionaryHelper;
use App\Helpers\CMW\Validation\CurrencyValidationHelper;
use App\Models\CMW\Master\Currency;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Config\Exception\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Currency')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'symbol' => '',
        'symbol_position' => 'BEFORE',
        'rate' => 1.00000,
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_symbol_position = [];

    public function mount(): void
    {
        $this->authorize('create currency');
        $this->dropdown_symbol_position = CurrencyDictionaryHelper::getSymbolPositions();
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:currencies,code',
            'inputs.name' => 'required|string|max:100|unique:currencies,name',
            'inputs.symbol' => 'required|string|max:10',
            'inputs.symbol_position' => 'required|in:'.CurrencyDictionaryHelper::getSymbolPositionValidationString(),
            'inputs.rate' => CurrencyValidationHelper::exchangeRateRules(),
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create currency');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Currency::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'symbol' => $validated['inputs']['symbol'],
                    'symbol_position' => $validated['inputs']['symbol_position'],
                    'rate' => $validated['inputs']['rate'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Currency created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.currency.refresh');
                $this->modal('create-currency')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Currency with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating currency', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.currency.create.open')]
    public function openModal(): void
    {
        $this->inputs = [
            'code' => '',
            'name' => '',
            'symbol' => '',
            'symbol_position' => 'BEFORE',
            'rate' => 1.00000,
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-currency')->show();
    }

    public function render()
    {
        return view('livewire.masters.currency.create');
    }
}
