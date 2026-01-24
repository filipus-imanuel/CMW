<?php

namespace App\Livewire\Masters\Company;

use App\Helpers\CMW\PopulateDataHelper;
use App\Helpers\CMW\Validation\CurrencyValidationHelper;
use App\Models\CMW\Master\Company;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Company')]
class Edit extends Component
{
    public ?Company $company = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'sales_limit' => 0,
        'currency_id' => 1,
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_currency = [];

    private function handlePopulateCurrency(): void
    {
        $this->dropdown_currency = PopulateDataHelper::getCurrencies();
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:companies,code,'.$this->company?->id,
            'inputs.name' => 'required|string|max:100|unique:companies,name,'.$this->company?->id,
            'inputs.sales_limit' => CurrencyValidationHelper::amountRules(0, 999999999999),
            'inputs.currency_id' => CurrencyValidationHelper::currencyIdEditRules($this->company?->currency_id ?? 1),
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit company');

        try {
            if ($this->company->is_edit_locked) {
                Flux::toast('This company cannot be edited.', variant: 'danger', position: 'top right');

                return;
            }

            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->company->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'sales_limit' => $validated['inputs']['sales_limit'],
                    'currency_id' => $validated['inputs']['currency_id'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Company updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.company.refresh');
                $this->modal('edit-company')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Company with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating company', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.company.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit company');
        $this->resetValidation();
        $this->handlePopulateCurrency();

        $this->company = Company::with('currency')->findOrFail($id);

        $this->inputs = [
            'code' => $this->company->code,
            'name' => $this->company->name,
            'sales_limit' => $this->company->sales_limit,
            'currency_id' => $this->company->currency_id,
            'remarks' => $this->company->remarks,
            'is_active' => $this->company->is_active,
        ];

        $this->modal('edit-company')->show();
    }

    public function render()
    {
        return view('livewire.masters.company.edit');
    }
}
