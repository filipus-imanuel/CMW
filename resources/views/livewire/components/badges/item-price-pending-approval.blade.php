<div 
    wire:poll.30s="refreshCount"
    x-data
    x-on:item-price-approval-refresh.window="$wire.refreshCount()"
>
    @if($count > 0)
        <flux:badge color="amber" size="sm">{{ $count }}</flux:badge>
    @endif
</div>
