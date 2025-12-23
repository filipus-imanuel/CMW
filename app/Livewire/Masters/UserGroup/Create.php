<?php

namespace App\Livewire\Masters\UserGroup;

use App\Models\CMW\Master\UserGroup;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create User Group')]
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
        $this->authorize('create user group');
    }

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:user_groups,code',
            'inputs.name' => 'required|string|max:255|unique:user_groups,name',
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->authorize('create user group');
        
        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                UserGroup::create([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'created_by' => Auth::id(),
                ]);

                Flux::toast('User Group created successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.user-group.refresh');
                $this->modal('create-user-group')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('User Group with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: ' . $e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to create user group: ' . $e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.user-group.create.open')]
    public function openModal(): void
    {
        $this->inputs = [
            'code' => '',
            'name' => '',
            'remarks' => '',
            'is_active' => true,
        ];
        $this->resetValidation();
        $this->modal('create-user-group')->show();
    }

    public function render()
    {
        return view('livewire.masters.user-group.create');
    }
}
