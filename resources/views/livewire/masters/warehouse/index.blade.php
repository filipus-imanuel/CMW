<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Warehouses</flux:heading>

        @can('create warehouse')
        <flux:button
            wire:click="$dispatch('cmw.master.warehouse.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Warehouse
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.warehouse.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.warehouse.create />
    <livewire:masters.warehouse.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-warehouse-confirmation">
        <flux:heading>Delete Warehouse</flux:heading>
        <flux:subheading>Are you sure you want to delete this warehouse? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-warehouse-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
