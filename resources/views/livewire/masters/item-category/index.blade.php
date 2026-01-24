<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Item Categories</flux:heading>

        @can('create item category')
        <flux:button
            wire:click="$dispatch('cmw.master.item-category.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Item Category
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.item-category.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.item-category.create />
    <livewire:masters.item-category.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-item-category-confirmation">
        <flux:heading>Delete Item Category</flux:heading>
        <flux:subheading>Are you sure you want to delete this item category? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-item-category-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
