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
        'payment_code' => '',
        'bank_name' => '',
        'bank_account_name' => '',
        'bank_account_number' => '',
        'currency_id' => 1,
        'tax_mode' => 'NONE',
        'tax_id' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public $dropdown_currency = [];

    public $dropdown_taxes = [];

    private function handlePopulateCurrency(): void
    {
        $this->dropdown_currency = PopulateDataHelper::getCurrencies();
    }

    private function handlePopulateTaxes(): void
    {
        $this->dropdown_taxes = PopulateDataHelper::getTaxes();
    }

    public function updatedInputsTaxMode($value): void
    {
        if ($value === 'NONE') {
            $this->inputs['tax_id'] = '';
        }
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:companies,code',
            'inputs.name' => 'required|string|max:100|unique:companies,name',
            'inputs.sales_limit' => CurrencyValidationHelper::amountRules(0, 999999999999),
            'inputs.payment_code' => 'nullable|string|max:2',
            'inputs.bank_name' => 'nullable|string|max:100',
            'inputs.bank_account_name' => 'nullable|string|max:100',
            'inputs.bank_account_number' => 'nullable|string|max:50',
            'inputs.currency_id' => CurrencyValidationHelper::currencyIdRules(),
            'inputs.tax_mode' => 'required|in:INCLUDE,EXCLUDE,NONE',
            'inputs.tax_id' => 'nullable|required_if:inputs.tax_mode,INCLUDE,EXCLUDE|exists:taxes,id',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function store(): void
    {
        $this->authorize('create company');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $taxMode = $validated['inputs']['tax_mode'];

                Company::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'sales_limit' => $validated['inputs']['sales_limit'],
                    'payment_code' => $validated['inputs']['payment_code'] ?: null,
                    'bank_name' => $validated['inputs']['bank_name'] ?: null,
                    'bank_account_name' => $validated['inputs']['bank_account_name'] ?: null,
                    'bank_account_number' => $validated['inputs']['bank_account_number'] ?: null,
                    'currency_id' => $validated['inputs']['currency_id'],
                    'tax_mode' => $taxMode,
                    'tax_id' => $taxMode !== 'NONE' ? $validated['inputs']['tax_id'] : null,
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
        $this->authorize('create company');

        $this->handlePopulateCurrency();
        $this->handlePopulateTaxes();
        $this->inputs = [
            'code' => '',
            'name' => '',
            'sales_limit' => 0,
            'payment_code' => '',
            'bank_name' => '',
            'bank_account_name' => '',
            'bank_account_number' => '',
            'currency_id' => 1, // Default to IDR
            'tax_mode' => 'NONE',
            'tax_id' => '',
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
