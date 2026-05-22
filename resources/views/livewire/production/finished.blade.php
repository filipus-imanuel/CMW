<div>
    <flux:heading size="xl" class="mb-6">Production - Finished</flux:heading>

    <div class="flex items-center justify-between mb-6">
        <flux:text>Sales orders that have completed delivery</flux:text>
    </div>

    <flux:card>
        <livewire:production.production-data-table mode="finished" />
    </flux:card>
</div>
