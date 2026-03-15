<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Payment Methods</flux:heading>
        @can('create payment method')
            <flux:button variant="primary" wire:click="$dispatch('cmw.master.payment-method.create.open')">
                <flux:icon.plus class="size-5" />
                Add Payment Method
            </flux:button>
        @endcan
    </div>

    <flux:separator class="my-4" />

    <flux:card>
        <livewire:masters.payment-method.index-data-table />
    </flux:card>

    @can('create payment method')
        <livewire:masters.payment-method.create />
    @endcan

    @can('edit payment method')
        <livewire:masters.payment-method.edit />
    @endcan

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-payment-method-confirmation">
        <flux:heading>Delete Payment Method</flux:heading>
        <flux:subheading>Are you sure you want to delete this payment method? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer />
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-payment-method-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
