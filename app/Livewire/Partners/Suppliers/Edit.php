<?php

namespace App\Livewire\Partners\Suppliers;

use App\Models\CMW\Master\Partner;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Supplier')]
class Edit extends Component
{
    public ?Partner $supplier = null;

    public $inputs = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:partners,code,'.$this->supplier?->id,
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

    #[On('cmw.partners.suppliers.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit supplier');

        $this->supplier = Partner::where('is_supplier', true)->findOrFail($id);

        $this->inputs['code'] = $this->supplier->code;
        $this->inputs['name'] = $this->supplier->name;
        $this->inputs['remarks'] = $this->supplier->remarks;
        $this->inputs['is_active'] = (bool) $this->supplier->is_active;

        $this->resetValidation();

        $this->modal('edit-supplier')->show();
    }

    public function save()
    {
        $this->authorize('edit supplier');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->supplier->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Supplier updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.suppliers.refresh');
        $this->modal('edit-supplier')->close();
    }

    public function render()
    {
        return view('livewire.partners.suppliers.edit');
    }
}
