<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Roles</flux:heading>
            <flux:subheading>Manage roles and permissions</flux:subheading>
        </div>
        @can('create role')
            <flux:button variant="primary" icon="plus" href="{{ route('employees.roles.create') }}" wire:navigate>
                Create Role
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <livewire:employees.role.index-data-table />
    </flux:card>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-role-confirmation" class="min-w-[22rem]">
        <form wire:submit="executeDelete" class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Role</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete the role
                    <strong>"{{ $deleteRoleName }}"</strong>?
                    @if ($deleteUserCount > 0)
                        <span class="text-red-600 dark:text-red-400">
                            This role is currently assigned to {{ $deleteUserCount }}
                            {{ Str::plural('user', $deleteUserCount) }}.
                        </span>
                    @endif
                </flux:subheading>
            </div>

            <flux:input wire:model="deletePassword" label="Confirm Password" type="password"
                placeholder="Enter your password to confirm" required />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('delete-role-confirmation').close()">
                    Cancel
                </flux:button>
                <flux:button variant="danger" type="submit">Delete Role</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
