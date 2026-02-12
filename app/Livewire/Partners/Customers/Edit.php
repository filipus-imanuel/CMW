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

#[Title('Edit Customer')]
class Edit extends Component
{
    public ?Partner $customer = null;

    public $inputs = [];

    public $dropdown_category_prices = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:partners,code,'.$this->customer?->id,
            'inputs.name' => 'required|string|max:100',
            'inputs.category_price_id' => 'nullable|exists:category_prices,id',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
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
    }

    #[On('cmw.partners.customers.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit customer');

        $this->customer = Partner::where('is_customer', true)->findOrFail($id);

        $this->inputs['code'] = $this->customer->code;
        $this->inputs['name'] = $this->customer->name;
        $this->inputs['category_price_id'] = $this->customer->category_price_id;
        $this->inputs['remarks'] = $this->customer->remarks;
        $this->inputs['is_active'] = (bool) $this->customer->is_active;

        $this->resetValidation();
        $this->loadDropdowns();

        $this->modal('edit-customer')->show();
    }

    public function update()
    {
        $this->authorize('edit customer');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->customer->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Customer updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.customers.refresh');
        $this->modal('edit-customer')->close();
    }

    public function render()
    {
        return view('livewire.partners.customers.edit');
    }
}
