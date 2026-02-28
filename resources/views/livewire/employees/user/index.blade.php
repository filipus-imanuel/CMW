<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Users</flux:heading>
            <flux:subheading>Manage system users</flux:subheading>
        </div>
        @can('create user')
            <flux:button variant="primary" icon="plus" href="{{ route('employees.users.create') }}" wire:navigate>
                Create User
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <livewire:employees.user.index-data-table />
    </flux:card>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-user-confirmation" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete User</flux:heading>
                <flux:subheading>Are you sure you want to delete this user? This action can be undone by an
                    administrator.</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('delete-user-confirmation').close()">
                    Cancel
                </flux:button>
                <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
