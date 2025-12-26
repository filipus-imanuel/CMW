<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">UOM Conversions</flux:heading>
        
        @can('create uom conversion')
        <flux:button 
            icon="plus" 
            variant="primary"
            wire:click="$dispatch('cmw.master.uom-conversion.create.open')"
        >
            Create UOM Conversion
        </flux:button>
        @endcan
    </div>

    {{-- Info --}}
    <flux:callout variant="info" icon="information-circle">
        UOM Conversions define how different units of measurement relate to each other. For example: 1 Dozen = 12 Pieces, or 1 Kilogram = 1000 Grams
    </flux:callout>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.uom-conversion.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.uom-conversion.create />
    <livewire:masters.uom-conversion.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-uom-conversion-confirmation">
        <flux:heading>Delete UOM Conversion</flux:heading>
        <flux:subheading>Are you sure you want to delete this UOM conversion? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-uom-conversion-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
