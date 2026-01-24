<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Item Price History</flux:heading>
        <flux:text class="text-zinc-500">Shows price changes from the last 30 days by default</flux:text>
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:inventories.history-item-price.index-data-table />
    </flux:card>
</div>
