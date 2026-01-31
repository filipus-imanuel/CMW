<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Item Price Approval History</flux:heading>
            <flux:text class="text-zinc-500">View history of approved and rejected price changes</flux:text>
        </div>

        <flux:button href="{{ route('inventories.item-price-approvals.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Approvals
        </flux:button>
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:inventories.item-price.approval-history-data-table />
    </flux:card>
</div>
