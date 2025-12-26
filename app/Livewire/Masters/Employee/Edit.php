<?php

namespace App\Livewire\Masters\Employee;

use App\Models\CMW\Master\Employee;
use App\Models\CMW\Master\Position;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Employee')]
class Edit extends Component
{
    public ?Employee $employee = null;

    public $inputs = [];

    public $positions = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:employees,code,'.$this->employee?->id,
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

    #[On('cmw.master.employee.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit employee');

        $this->employee = Employee::findOrFail($id);

        $this->inputs['code'] = $this->employee->code;
        $this->inputs['name'] = $this->employee->name;
        $this->inputs['position_id'] = $this->employee->position_id;
        $this->inputs['email'] = $this->employee->email;
        $this->inputs['phone'] = $this->employee->phone;
        $this->inputs['address'] = $this->employee->address;
        $this->inputs['remarks'] = $this->employee->remarks;
        $this->inputs['is_active'] = $this->employee->is_active;

        $this->resetValidation();
        $this->loadPositions();

        $this->modal('edit-employee')->show();
    }

    public function loadPositions()
    {
        $this->positions = Position::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($position) => [
                'value' => $position->id,
                'label' => $position->name,
            ])
            ->toArray();
    }

    public function save()
    {
        $this->authorize('edit employee');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->employee->update([
                ...$validated['inputs'],
                'updated_by' => Auth::id(),
            ]);
        });

        Flux::toast('Employee updated successfully', variant: 'success', position: 'top-end');

        $this->dispatch('cmw.master.employee.refresh');
        $this->modal('edit-employee')->close();
    }

    public function render()
    {
        return view('livewire.masters.employee.edit');
    }
}
