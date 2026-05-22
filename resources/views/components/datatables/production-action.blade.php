@props([
    'rowId' => null,
    'productionStatus' => 'ongoing',
    'canEdit' => false,
    'canCreateAdjustment' => false,
])
<div class="text-center flex items-center justify-center gap-1">
    @if($canEdit)
        <flux:button href="{{ route('production.edit', ['id' => $rowId]) }}" wire:navigate icon="pencil-square" size="xs" />
    @endif
    @if($productionStatus === 'ongoing' && $canCreateAdjustment)
        <flux:tooltip content="Create Stock Adjustment">
            <flux:button
                href="{{ route('inventories.stock-adjustments.create', ['order_header_id' => $rowId]) }}"
                wire:navigate
                icon="plus-circle"
                size="xs"
                variant="primary" />
        </flux:tooltip>
    @endif
</div>
