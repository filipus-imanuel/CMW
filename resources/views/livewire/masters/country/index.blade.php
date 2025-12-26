<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Countries</flux:heading>

        @can('create country')
        <flux:button
            wire:click="$dispatch('cmw.master.country.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Country
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.country.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.country.create />
    <livewire:masters.country.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-country-confirmation">
        <flux:heading>Delete Country</flux:heading>
        <flux:subheading>Are you sure you want to delete this country? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-country-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
