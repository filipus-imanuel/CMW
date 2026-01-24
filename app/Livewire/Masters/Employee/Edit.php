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

#[Title('Edit Employee')]
class Edit extends Component
{
    public ?Employee $employee = null;

    public $inputs = [];

    public $dropdown_department = [];

    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:employees,code,'.$this->employee?->id,
            'inputs.name' => 'required|string|max:100',
            'inputs.department_id' => 'required|exists:departments,id',
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
            'inputs.department_id.required' => 'Department is required',
            'inputs.email.email' => 'Please enter a valid email address',
        ];
    }

    private function handlePopulateDepartment(): void
    {
        $this->dropdown_department = PopulateDataHelper::getDepartments();
    }

    #[On('cmw.master.employee.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit employee');

        $this->employee = Employee::findOrFail($id);

        $this->inputs['code'] = $this->employee->code;
        $this->inputs['name'] = $this->employee->name;
        $this->inputs['department_id'] = $this->employee->department_id;
        $this->inputs['email'] = $this->employee->email;
        $this->inputs['phone'] = $this->employee->phone;
        $this->inputs['address'] = $this->employee->address;
        $this->inputs['remarks'] = $this->employee->remarks;
        $this->inputs['is_active'] = (bool) $this->employee->is_active;

        $this->resetValidation();
        $this->handlePopulateDepartment();

        $this->modal('edit-employee')->show();
    }

    public function update()
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
