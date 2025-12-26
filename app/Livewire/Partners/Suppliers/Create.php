<?php

namespace App\Livewire\Partners\Suppliers;

use App\Models\CMW\Master\Partner;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Supplier')]
class Create extends Component
{
    public $inputs = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:partners,code',
            'inputs.name' => 'required|string|max:100',
            'inputs.remarks' => 'nullable|string|max:500',
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

    #[On('cmw.partners.suppliers.create.open')]
    public function openModal()
    {
        $this->authorize('create supplier');

        $this->reset(['inputs']);
        $this->resetValidation();

        $this->modal('create-supplier')->show();
    }

    public function save()
    {
        $this->authorize('create supplier');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            Partner::create([
                ...$validated['inputs'],
                'is_supplier' => true,
                'is_customer' => false,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        });

        Flux::toast('Supplier created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.partners.suppliers.refresh');
        $this->modal('create-supplier')->close();
    }

    public function render()
    {
        return view('livewire.partners.suppliers.create');
    }
}
