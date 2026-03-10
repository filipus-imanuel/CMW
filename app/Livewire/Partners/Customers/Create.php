<?php

namespace App\Livewire\Partners\Customers;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\Partner;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Customer')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_category_prices = [];

    public $dropdown_companies = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:partners,code',
            'inputs.name' => 'required|string|max:100',
            'inputs.category_price_id' => 'nullable|exists:category_prices,id',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.company_ids' => 'nullable|array',
            'inputs.company_ids.*' => 'exists:companies,id',
        ];
    }

    public function messages()
    {
        return [
            'inputs.code.required' => 'Code is required',
            'inputs.code.unique' => 'This code already exists',
            'inputs.name.required' => 'Name is required',
        ];
    }

    private function loadDropdowns(): void
    {
        $this->dropdown_category_prices = PopulateDataHelper::getCategoryPrices(['labelFormat' => 'code_name']);
        $this->dropdown_companies = PopulateDataHelper::getCompanies(['useCache' => false]);
    }

    #[On('cmw.partners.customers.create.open')]
    public function openModal()
    {
        $this->authorize('create customer');

        $this->reset(['inputs']);
        $this->inputs['company_ids'] = [];
        $this->resetValidation();
        $this->loadDropdowns();

        $this->modal('create-customer')->show();
    }

    public function store()
    {
        $this->authorize('create customer');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $partner = Partner::create([
                ...$validated['inputs'],
                'is_supplier' => false,
                'is_customer' => true,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            $partner->companies()->sync($validated['inputs']['company_ids'] ?? []);
        });

        Flux::toast('Customer created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.customers.refresh');
        $this->modal('create-customer')->close();
    }

    public function render()
    {
        return view('livewire.partners.customers.create');
    }
}
