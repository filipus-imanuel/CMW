<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Category Prices</flux:heading>

        @can('create category price')
        <flux:button
            wire:click="$dispatch('cmw.master.category-price.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Category Price
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.category-price.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.category-price.create />
    <livewire:masters.category-price.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-category-price-confirmation">
        <flux:heading>Delete Category Price</flux:heading>
        <flux:subheading>Are you sure you want to delete this category price? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-category-price-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
