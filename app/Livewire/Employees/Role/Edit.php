<?php

namespace App\Livewire\Employees\Role;

use App\Helpers\CMW\PermissionHelper;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('Edit Role')]
class Edit extends Component
{
    public ?Role $role = null;

    public string $roleName = '';

    public array $selectedPermissions = [];

    public string $search = '';

    protected array $protectedRoles = ['Super Admin'];

    /**
     * Module map: readable label => key from PermissionHelper::master().
     *
     * @var array<string, string>
     */
    protected array $moduleMap = [
        'Master Data' => 'master',
        'Partners' => 'partners',
        'Inventory' => 'inventory',
        'System' => 'system',
        'Sales' => 'sales',
        'Warehouse' => 'warehouse',
        'Employee' => 'employee',
        'Extra Permissions' => 'extra',
    ];

    public function mount($id): void
    {
        $this->authorize('edit role');

        $this->role = Role::findOrFail($id);
        $this->roleName = $this->role->name;

        // Load currently assigned permissions
        $this->selectedPermissions = $this->role->permissions
            ->pluck('name')
            ->toArray();
    }

    #[Computed]
    public function modules(): array
    {
        $master = PermissionHelper::master();
        $modules = [];

        foreach ($this->moduleMap as $label => $key) {
            if (! isset($master[$key])) {
                continue;
            }

            $resources = [];
            foreach ($master[$key] as $resource => $actions) {
                $permissions = [];
                foreach ($actions as $action) {
                    $permissionName = "{$action} {$resource}";
                    $permissions[] = [
                        'name' => $permissionName,
                        'action' => $action,
                        'resource' => $resource,
                    ];
                }
                $resources[$resource] = $permissions;
            }
            $modules[$label] = $resources;
        }

        return $modules;
    }

    #[Computed]
    public function filteredModules(): array
    {
        $modules = $this->modules;

        if (empty($this->search)) {
            return $modules;
        }

        $search = strtolower($this->search);
        $filtered = [];

        foreach ($modules as $moduleLabel => $resources) {
            // Check if module label matches
            if (str_contains(strtolower($moduleLabel), $search)) {
                $filtered[$moduleLabel] = $resources;

                continue;
            }

            $filteredResources = [];
            foreach ($resources as $resource => $permissions) {
                // Check if resource name or any permission matches
                if (str_contains(strtolower($resource), $search)) {
                    $filteredResources[$resource] = $permissions;

                    continue;
                }

                foreach ($permissions as $permission) {
                    if (str_contains(strtolower($permission['action']), $search)
                        || str_contains(strtolower($permission['name']), $search)) {
                        $filteredResources[$resource] = $permissions;
                        break;
                    }
                }
            }

            if (! empty($filteredResources)) {
                $filtered[$moduleLabel] = $filteredResources;
            }
        }

        return $filtered;
    }

    #[Computed]
    public function totalPermissions(): int
    {
        return Permission::where('guard_name', 'web')->count();
    }

    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selectedPermissions);
    }

    public function togglePermission(string $permission): void
    {
        if (in_array($permission, $this->selectedPermissions)) {
            $this->selectedPermissions = array_values(
                array_diff($this->selectedPermissions, [$permission])
            );
        } else {
            $this->selectedPermissions[] = $permission;
        }
    }

    public function selectAllModule(string $moduleLabel): void
    {
        $modules = $this->modules;

        if (! isset($modules[$moduleLabel])) {
            return;
        }

        foreach ($modules[$moduleLabel] as $resource => $permissions) {
            foreach ($permissions as $permission) {
                if (! in_array($permission['name'], $this->selectedPermissions)) {
                    $this->selectedPermissions[] = $permission['name'];
                }
            }
        }
    }

    public function deselectAllModule(string $moduleLabel): void
    {
        $modules = $this->modules;

        if (! isset($modules[$moduleLabel])) {
            return;
        }

        $toRemove = [];
        foreach ($modules[$moduleLabel] as $resource => $permissions) {
            foreach ($permissions as $permission) {
                $toRemove[] = $permission['name'];
            }
        }

        $this->selectedPermissions = array_values(
            array_diff($this->selectedPermissions, $toRemove)
        );
    }

    public function selectAllResource(string $moduleLabel, string $resource): void
    {
        $modules = $this->modules;

        if (! isset($modules[$moduleLabel][$resource])) {
            return;
        }

        foreach ($modules[$moduleLabel][$resource] as $permission) {
            if (! in_array($permission['name'], $this->selectedPermissions)) {
                $this->selectedPermissions[] = $permission['name'];
            }
        }
    }

    public function deselectAllResource(string $moduleLabel, string $resource): void
    {
        $modules = $this->modules;

        if (! isset($modules[$moduleLabel][$resource])) {
            return;
        }

        $toRemove = [];
        foreach ($modules[$moduleLabel][$resource] as $permission) {
            $toRemove[] = $permission['name'];
        }

        $this->selectedPermissions = array_values(
            array_diff($this->selectedPermissions, $toRemove)
        );
    }

    public function selectAll(): void
    {
        $allPermissions = Permission::where('guard_name', 'web')
            ->pluck('name')
            ->toArray();

        $this->selectedPermissions = $allPermissions;
    }

    public function deselectAll(): void
    {
        $this->selectedPermissions = [];
    }

    public function update(): void
    {
        $this->authorize('edit role');

        DB::transaction(function () {
            // Update role name if not protected
            if (! in_array($this->role->name, $this->protectedRoles)) {
                $this->validate([
                    'roleName' => 'required|string|max:255|unique:roles,name,'.$this->role->id,
                ]);
                $this->role->update(['name' => $this->roleName]);
            }

            // Sync permissions
            $this->role->syncPermissions($this->selectedPermissions);
        });

        Flux::toast('Role permissions updated successfully', variant: 'success', position: 'top right');

        $this->redirect(route('employees.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.role.edit');
    }
}
