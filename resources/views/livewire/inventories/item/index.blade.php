<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Items</flux:heading>
        
        @can('create item')
        <flux:button 
            icon="plus" 
            variant="primary"
            wire:click="$dispatch('cmw.inventories.item.create.open')"
        >
            Create Item
        </flux:button>
        @endcan
    </div>

    {{-- Info --}}
    <flux:callout variant="info" icon="information-circle">
        Items are products or materials tracked in your inventory system. Types include Raw Materials, Work In Process, Finished Goods, and Spare Parts.
    </flux:callout>

    {{-- DataTable --}}
    <flux:card>
        <livewire:inventories.item.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:inventories.item.create />
    <livewire:inventories.item.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-item-confirmation">
        <flux:heading>Delete Item</flux:heading>
        <flux:subheading>Are you sure you want to delete this item? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-item-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
