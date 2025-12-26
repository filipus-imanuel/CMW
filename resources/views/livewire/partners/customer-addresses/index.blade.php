<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Customer Addresses</flux:heading>
        
        @can('create partner address')
        <flux:button 
            icon="plus" 
            variant="primary"
            wire:click="$dispatch('cmw.partners.customer-addresses.create.open')"
        >
            Create Address
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:partners.customer-addresses.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:partners.customer-addresses.create />
    <livewire:partners.customer-addresses.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-customer-address-confirmation">
        <flux:heading>Delete Customer Address</flux:heading>
        <flux:subheading>Are you sure you want to delete this customer address? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-customer-address-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
