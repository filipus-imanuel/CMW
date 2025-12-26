<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Units of Measure</flux:heading>

        @can('create uom')
        <flux:button
            wire:click="$dispatch('cmw.master.uom.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Unit
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.uom.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.uom.create />
    <livewire:masters.uom.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-uom-confirmation">
        <flux:heading>Delete Unit of Measure</flux:heading>
        <flux:subheading>Are you sure you want to delete this unit of measure? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-uom-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
