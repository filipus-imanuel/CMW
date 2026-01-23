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

#[Title('Create Company')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'sales_limit' => 0,
        'currency_id' => 1,
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_currency = [];

    public function mount(): void
    {
        $this->authorize('create company');
        $this->handlePopulateCurrency();
    }

    private function handlePopulateCurrency(): void
    {
        $this->dropdown_currency = PopulateDataHelper::getCurrencies();
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:companies,code',
            'inputs.name' => 'required|string|max:100|unique:companies,name',
            'inputs.sales_limit' => CurrencyValidationHelper::amountRules(0, 999999999999),
            'inputs.currency_id' => CurrencyValidationHelper::currencyIdRules(),
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create company');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Company::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'sales_limit' => $validated['inputs']['sales_limit'],
                    'currency_id' => $validated['inputs']['currency_id'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Company created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.company.refresh');
                $this->modal('create-company')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Company with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating company', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.company.create.open')]
    public function openModal(): void
    {
        $this->handlePopulateCurrency();
        $this->inputs = [
            'code' => '',
            'name' => '',
            'sales_limit' => 0,
            'currency_id' => 1, // Default to IDR
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-company')->show();
    }

    public function render()
    {
        return view('livewire.masters.company.create');
    }
}
