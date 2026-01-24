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

#[Title('Create Department')]
class Create extends Component
{
    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function mount(): void
    {
        $this->authorize('create department');
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:departments,code',
            'inputs.name' => 'required|string|max:100|unique:departments,name',
            'inputs.remarks' => 'nullable|string|max:1024',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create department');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                Department::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('Department created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.department.refresh');
                $this->modal('create-department')->close();
            });
        } catch (ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (QueryException $e) {
            Flux::toast('Department with this name or code already exists', variant: 'danger', position: 'top right');
            throw $e;
        } catch (Exception $e) {
            Flux::toast('An error occurred while creating department', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.department.create.open')]
    public function openModal(): void
    {
        $this->inputs = [
            'code' => '',
            'name' => '',
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-department')->show();
    }

    public function render()
    {
        return view('livewire.masters.department.create');
    }
}
