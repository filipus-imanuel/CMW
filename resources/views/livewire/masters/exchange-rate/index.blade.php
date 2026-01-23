<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Exchange Rates</flux:heading>
        @can('create exchange rate')
            <flux:button variant="primary" wire:click="$dispatch('cmw.master.exchange-rate.create.open')">
                <flux:icon.plus class="size-5" />
                Add Exchange Rate
            </flux:button>
        @endcan
    </div>

    <flux:separator class="my-4" />

    <flux:callout color="blue" icon="information-circle" class="mb-4">
        <flux:callout.text>Exchange rates are automatically created in pairs. When you create a rate from Currency A to Currency B, the reciprocal rate (B to A) is automatically calculated and created.</flux:callout.text>
    </flux:callout>

    <flux:card>
        <livewire:masters.exchange-rate.index-data-table />
    </flux:card>

    @can('create exchange rate')
        <livewire:masters.exchange-rate.create />
    @endcan

    @can('edit exchange rate')
        <livewire:masters.exchange-rate.edit />
    @endcan

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-exchange-rate-confirmation">
        <flux:heading>Delete Exchange Rate</flux:heading>
        <flux:subheading>Are you sure you want to delete this exchange rate? This action cannot be undone. Note: The reciprocal rate will NOT be automatically deleted.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer />
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-exchange-rate-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
