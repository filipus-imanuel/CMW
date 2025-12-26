<?php

namespace App\Livewire\Partners\Customers;

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

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:partners,code,'.$this->customer?->id,
            'inputs.name' => 'required|string|max:100',
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

    #[On('cmw.partners.customers.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit customer');

        $this->customer = Partner::where('is_customer', true)->findOrFail($id);

        $this->inputs['code'] = $this->customer->code;
        $this->inputs['name'] = $this->customer->name;
        $this->inputs['remarks'] = $this->customer->remarks;
        $this->inputs['is_active'] = $this->customer->is_active;

        $this->resetValidation();

        $this->modal('edit-customer')->show();
    }

    public function save()
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
