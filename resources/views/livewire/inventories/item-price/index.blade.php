<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Item Prices</flux:heading>

        @can('create item price')
        <flux:button
            wire:click="$dispatch('cmw.inventories.item-price.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Item Price
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:inventories.item-price.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:inventories.item-price.create />
    <livewire:inventories.item-price.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-item-price-confirmation">
        <flux:heading>Delete Item Price</flux:heading>
        <flux:subheading>Are you sure you want to delete this item price? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-item-price-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
