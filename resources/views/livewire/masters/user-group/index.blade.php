<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">User Groups</flux:heading>

        @can('create user group')
        <flux:button
            wire:click="$dispatch('cmw.master.user-group.create.open')"
            variant="primary"
            icon="plus"
        >
            Create User Group
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.user-group.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.user-group.create />
    <livewire:masters.user-group.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-user-group-confirmation">
        <flux:heading>Delete User Group</flux:heading>
        <flux:subheading>Are you sure you want to delete this user group? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-user-group-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
