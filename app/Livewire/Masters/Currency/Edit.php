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

#[Title('Edit Currency')]
class Edit extends Component
{
    public ?Currency $currency = null;

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

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:currencies,code,'.$this->currency?->id,
            'inputs.name' => 'required|string|max:100|unique:currencies,name,'.$this->currency?->id,
            'inputs.symbol' => 'required|string|max:10',
            'inputs.symbol_position' => 'required|in:'.CurrencyDictionaryHelper::getSymbolPositionValidationString(),
            'inputs.rate' => CurrencyValidationHelper::exchangeRateRules(),
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit currency');

        try {
            if ($this->currency->is_edit_locked) {
                Flux::toast('This currency cannot be edited.', variant: 'danger', position: 'top right');

                return;
            }

            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->currency->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'symbol' => $validated['inputs']['symbol'],
                    'symbol_position' => $validated['inputs']['symbol_position'],
                    'rate' => $validated['inputs']['rate'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Currency updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.currency.refresh');
                $this->modal('edit-currency')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Currency with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating currency', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.currency.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit currency');
        $this->resetValidation();

        $this->dropdown_symbol_position = CurrencyDictionaryHelper::getSymbolPositions();
        $this->currency = Currency::findOrFail($id);

        $this->inputs = [
            'code' => $this->currency->code,
            'name' => $this->currency->name,
            'symbol' => $this->currency->symbol,
            'symbol_position' => $this->currency->symbol_position,
            'rate' => $this->currency->rate,
            'remarks' => $this->currency->remarks,
            'is_active' => $this->currency->is_active,
        ];

        $this->modal('edit-currency')->show();
    }

    public function render()
    {
        return view('livewire.masters.currency.edit');
    }
}
