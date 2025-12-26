<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Positions</flux:heading>

        @can('create position')
        <flux:button
            wire:click="$dispatch('cmw.master.position.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Position
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.position.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.position.create />
    <livewire:masters.position.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-position-confirmation">
        <flux:heading>Delete Position</flux:heading>
        <flux:subheading>Are you sure you want to delete this position? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-position-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
