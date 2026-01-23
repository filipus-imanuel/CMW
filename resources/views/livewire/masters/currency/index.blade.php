<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Currencies</flux:heading>

        @can('create currency')
        <flux:button
            wire:click="$dispatch('cmw.master.currency.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Currency
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.currency.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.currency.create />
    <livewire:masters.currency.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-currency-confirmation">
        <flux:heading>Delete Currency</flux:heading>
        <flux:subheading>Are you sure you want to delete this currency? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-currency-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
