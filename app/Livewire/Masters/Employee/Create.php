<?php

namespace App\Livewire\Masters\Employee;

use App\Helpers\CMW\PopulateDataHelper;
use App\Models\CMW\Master\Employee;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Employee')]
class Create extends Component
{
    public $inputs = [];

    public $dropdown_position = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:employees,code',
            'inputs.name' => 'required|string|max:100',
            'inputs.position_id' => 'required|exists:positions,id',
            'inputs.email' => 'nullable|email|max:100',
            'inputs.phone' => 'nullable|string|max:20',
            'inputs.address' => 'nullable|string|max:500',
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
            'inputs.position_id.required' => 'Position is required',
            'inputs.email.email' => 'Please enter a valid email address',
        ];
    }

    private function handlePopulatePosition(): void
    {
        $this->dropdown_position = PopulateDataHelper::getPositions();

        // Set default value to first item (index 0 since no prependDefault)
        $this->inputs['position_id'] = $this->dropdown_position[0]['value'] ?? null;
    }

    #[On('cmw.master.employee.create.open')]
    public function openModal()
    {
        $this->authorize('create employee');

        $this->reset(['inputs']);
        $this->inputs['is_active'] = true;
        $this->resetValidation();

        $this->handlePopulatePosition();

        $this->modal('create-employee')->show();
    }

    public function save()
    {
        $this->authorize('create employee');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            Employee::create([
                ...$validated['inputs'],
                'created_by' => Auth::id(),
            ]);
        });

        Flux::toast('Employee created successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.master.employee.refresh');
        $this->modal('create-employee')->close();
    }

    public function render()
    {
        return view('livewire.masters.employee.create');
    }
}
