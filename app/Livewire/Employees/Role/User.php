<?php

namespace App\Livewire\Employees\Role;

use App\Models\User as UserModel;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Manage Role Users')]
class User extends Component
{
    public ?Role $role = null;

    public array $selectedUsers = [];

    public string $search = '';

    public function mount($id): void
    {
        $this->authorize('edit role');

        $this->role = Role::findOrFail($id);

        // Load currently assigned users
        $this->selectedUsers = $this->role->users()
            ->pluck('id')
            ->toArray();
    }

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return UserModel::query()
            ->where('id', '>', 1)
            ->where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function filteredUsers(): \Illuminate\Support\Collection
    {
        $users = $this->users;

        if (empty($this->search)) {
            return $users;
        }

        $search = strtolower($this->search);

        return $users->filter(function ($user) use ($search) {
            return str_contains(strtolower($user->name), $search)
                || str_contains(strtolower($user->email), $search)
                || ($user->department && str_contains(strtolower($user->department->name), $search));
        });
    }

    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selectedUsers);
    }

    public function toggleUser(int $userId): void
    {
        if (in_array($userId, $this->selectedUsers)) {
            $this->selectedUsers = array_values(
                array_diff($this->selectedUsers, [$userId])
            );
        } else {
            $this->selectedUsers[] = $userId;
        }
    }

    public function selectAll(): void
    {
        $this->selectedUsers = $this->filteredUsers->pluck('id')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedUsers = [];
    }

    public function syncUsers(): void
    {
        $this->authorize('edit role');

        DB::transaction(function () {
            $currentUserIds = $this->role->users()->pluck('id')->toArray();
            $newUserIds = $this->selectedUsers;

            // Users to add
            $toAdd = array_diff($newUserIds, $currentUserIds);
            foreach ($toAdd as $userId) {
                $user = UserModel::find($userId);
                if ($user) {
                    $user->assignRole($this->role);
                }
            }

            // Users to remove
            $toRemove = array_diff($currentUserIds, $newUserIds);
            foreach ($toRemove as $userId) {
                $user = UserModel::find($userId);
                if ($user) {
                    $user->removeRole($this->role);
                }
            }
        });

        Flux::toast('User assignments updated successfully', variant: 'success', position: 'top right');

        $this->redirect(route('employees.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.role.user');
    }
}
