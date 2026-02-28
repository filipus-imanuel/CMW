<div>
    <div class="mb-6">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('employees.roles.index') }}" wire:navigate>
            Back to Roles
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Create New Role</flux:heading>

    <flux:card>
        <form wire:submit="store" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="inputs.name" label="Role Name" placeholder="e.g. Sales Manager" required />

                <flux:select wire:model="inputs.copy_from_role_id" label="Copy Permissions From"
                    placeholder="Select a role to copy (optional)..." searchable clearable>
                    @foreach ($availableRoles as $role)
                        <flux:select.option value="{{ $role['value'] }}">
                            {{ $role['label'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" href="{{ route('employees.roles.index') }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button variant="primary" type="submit">
                    Create Role
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
