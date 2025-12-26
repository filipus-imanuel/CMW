<div>
    <flux:heading size="xl" class="mb-6">Suppliers</flux:heading>

    <div class="flex items-center justify-between mb-6">
        <flux:text>Manage your suppliers</flux:text>
        @can('create supplier')
            <flux:button wire:click="$dispatch('cmw.partners.suppliers.create.open')" variant="primary" icon="plus">
                Add Supplier
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <livewire:partners.suppliers.index-data-table />
    </flux:card>

    <livewire:partners.suppliers.create />
    <livewire:partners.suppliers.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-supplier-confirmation">
        <flux:heading>Delete Supplier</flux:heading>
        <flux:subheading>Are you sure you want to delete this supplier? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-supplier-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
