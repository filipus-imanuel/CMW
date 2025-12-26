<div>
    <flux:heading size="xl" class="mb-6">Customers</flux:heading>

    <div class="flex items-center justify-between mb-6">
        <flux:text>Manage your customers</flux:text>
        @can('create customer')
            <flux:button wire:click="$dispatch('cmw.partners.customers.create.open')" variant="primary" icon="plus">
                Add Customer
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <livewire:partners.customers.index-data-table />
    </flux:card>

    <livewire:partners.customers.create />
    <livewire:partners.customers.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-customer-confirmation">
        <flux:heading>Delete Customer</flux:heading>
        <flux:subheading>Are you sure you want to delete this customer? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-customer-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
