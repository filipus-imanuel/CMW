<?php

namespace App\Livewire\Masters\UserGroup;

use App\Models\CMW\Master\UserGroup;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit User Group')]
class Edit extends Component
{
    public ?UserGroup $userGroup = null;

    public $inputs = [
        'code' => '',
        'name' => '',
        'remarks' => '',
        'is_active' => true,
    ];

    public function rules(): array
    {
        return [
            'inputs.code' => 'required|string|max:50|unique:user_groups,code,'.$this->userGroup?->id,
            'inputs.name' => 'required|string|max:255|unique:user_groups,name,'.$this->userGroup?->id,
            'inputs.remarks' => 'nullable|string|max:500',
            'inputs.is_active' => 'boolean',
        ];
    }

    public function update(): void
    {
        $this->authorize('edit user group');

        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $this->userGroup->update([
                    'code' => $validated['inputs']['code'],
                    'name' => $validated['inputs']['name'],
                    'remarks' => $validated['inputs']['remarks'],
                    'is_active' => $validated['inputs']['is_active'],
                    'updated_by' => Auth::id(),
                ]);

                Flux::toast('User Group updated successfully', variant: 'success', position: 'top right');
                $this->dispatch('cmw.master.user-group.refresh');
                $this->modal('edit-user-group')->close();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Flux::toast('Please fix the validation errors', variant: 'danger', position: 'top right');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Flux::toast('User Group with this name or code already exists', variant: 'danger', position: 'top right');
            } else {
                Flux::toast('Database error: '.$e->getMessage(), variant: 'danger', position: 'top right');
            }
            throw $e;
        } catch (\Exception $e) {
            Flux::toast('Failed to update user group: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    #[On('cmw.master.user-group.edit.open')]
    public function openModal($id): void
    {
        $this->authorize('edit user group');
        $this->resetValidation();

        $this->userGroup = UserGroup::findOrFail($id);

        $this->inputs = [
            'code' => $this->userGroup->code,
            'name' => $this->userGroup->name,
            'remarks' => $this->userGroup->remarks,
            'is_active' => $this->userGroup->is_active,
        ];

        $this->modal('edit-user-group')->show();
    }

    public function render()
    {
        return view('livewire.masters.user-group.edit');
    }
}
