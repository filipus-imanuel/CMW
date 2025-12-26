<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Credit Terms</flux:heading>
        
        @can('create credit term')
        <flux:button 
            icon="plus" 
            variant="primary"
            wire:click="$dispatch('cmw.master.credit-term.create.open')"
        >
            Create Credit Term
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.credit-term.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.credit-term.create />
    <livewire:masters.credit-term.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-credit-term-confirmation">
        <flux:heading>Delete Credit Term</flux:heading>
        <flux:subheading>Are you sure you want to delete this credit term? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-credit-term-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
