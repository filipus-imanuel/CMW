<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Stock Adjustments</flux:heading>

        @can('create stock adjustment')
        <flux:button
            icon="plus"
            variant="primary"
            :href="route('inventories.stock-adjustments.create')"
            wire:navigate
        >
            Create Adjustment
        </flux:button>
        @endcan
    </div>

    {{-- Info --}}
    <flux:callout variant="info" icon="information-circle">
        Stock adjustments are used to reconcile physical inventory counts with system records. Create an adjustment, add items with actual quantities, then confirm to update inventory ledger.
    </flux:callout>

    {{-- DataTable --}}
    <flux:card>
        <livewire:inventories.adjustment.index-data-table />
    </flux:card>

</div>
