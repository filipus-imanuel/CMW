<?php

namespace App\Livewire\Masters\Department;

use App\Models\CMW\Master\Department;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Department')]
class Edit extends Component
{
    public ?Department $department = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:departments,code,'.$this->department?->id,
            'inputs.name' => 'required|string|max:100|unique:departments,name,'.$this->department?->id,
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit department');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->department->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('Department updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.department.refresh');
                $this->modal('edit-department')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Department with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while updating department', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.department.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit department');
        $this->resetValidation();

        $this->department = Department::findOrFail($id);

        $this->inputs = [
            'code' => $this->department->code,
            'name' => $this->department->name,
            'remarks' => $this->department->remarks,
            'is_active' => $this->department->is_active,
        ];

        $this->modal('edit-department')->show();
    }

    public function render()
    {
        return view('livewire.masters.department.edit');
    }
}
