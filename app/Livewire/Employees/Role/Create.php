<?php

namespace App\Livewire\Employees\Role;

use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Create Role')]
class Create extends Component
{
    public $inputs = [];

    public $availableRoles = [];

    public function rules(): array
    {
        return [
            'inputs.name' => 'required|string|max:255|unique:roles,name',
            'inputs.copy_from_role_id' => 'nullable|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'inputs.name.required' => 'Role name is required',
            'inputs.name.unique' => 'This role name is already in use',
        ];
    }

    public function mount(): void
    {
        $this->authorize('create role');

        $this->inputs = [
            'name' => '',
            'copy_from_role_id' => '',
        ];

        $this->availableRoles = Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'Super Admin')
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => ['value' => $role->id, 'label' => $role->name])
            ->toArray();
    }

    public function store(): void
    {
        $this->authorize('create role');

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['inputs']['name'],
                'guard_name' => 'web',
            ]);

            // Copy permissions from source role if selected
            if (! empty($validated['inputs']['copy_from_role_id'])) {
                $sourceRole = Role::find($validated['inputs']['copy_from_role_id']);
                if ($sourceRole) {
                    $role->syncPermissions($sourceRole->permissions);
                }
            }
        });

        Flux::toast('Role created successfully', variant: 'success', position: 'top right');

        $this->redirect(route('employees.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.role.create');
    }
}
