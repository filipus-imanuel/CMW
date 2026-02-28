<?php

namespace App\Livewire\Employees\Role;

use Exception;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Roles')]
class Index extends Component
{
    public $deleteId = null;

    public $deletePassword = '';

    public $deleteRoleName = '';

    public $deleteUserCount = 0;

    protected array $protectedRoles = ['Super Admin'];

    #[On('cmw.employee.role.refresh')]
    public function refresh(): void
    {
        // This method exists to trigger component refresh
    }

    #[On('cmw.employee.role.delete')]
    public function confirmDelete($id): void
    {
        $this->authorize('delete role');

        $role = Role::findOrFail($id);

        if (in_array($role->name, $this->protectedRoles)) {
            Flux::toast('Cannot delete protected role.', variant: 'danger', position: 'top right');

            return;
        }

        $this->deleteId = $id;
        $this->deleteRoleName = $role->name;
        $this->deleteUserCount = $role->users()->count();
        $this->deletePassword = '';
        $this->modal('delete-role-confirmation')->show();
    }

    public function executeDelete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $this->authorize('delete role');

        $this->validate([
            'deletePassword' => 'required|string',
        ], [
            'deletePassword.required' => 'Please enter your password to confirm deletion.',
        ]);

        if (! Hash::check($this->deletePassword, auth()->user()->password)) {
            $this->addError('deletePassword', 'The password is incorrect.');

            return;
        }

        try {
            DB::transaction(function () {
                $role = Role::findOrFail($this->deleteId);

                if (in_array($role->name, $this->protectedRoles)) {
                    throw new Exception('Cannot delete protected role.');
                }

                // Remove all users from this role before deleting
                $role->users()->each(function ($user) use ($role) {
                    $user->removeRole($role);
                });

                $role->delete();

                Flux::toast("Role '{$this->deleteRoleName}' deleted successfully", variant: 'success', position: 'top right');
                $this->dispatch('cmw.employee.role.refresh');
            });

            $this->deleteId = null;
            $this->deleteRoleName = '';
            $this->deleteUserCount = 0;
            $this->deletePassword = '';
            $this->modal('delete-role-confirmation')->close();
        } catch (Exception $e) {
            Flux::toast('An error occurred: '.$e->getMessage(), variant: 'danger', position: 'top right');
        }
    }

    public function render()
    {
        return view('livewire.employees.role.index');
    }
}
