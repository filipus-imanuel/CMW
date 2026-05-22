<div>
    <div class="mb-6">
        <flux:button :href="route('production.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Production
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Edit Production Status</flux:heading>

    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Sales Order Summary</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <flux:text class="text-sm text-zinc-500">SO Code</flux:text>
                <flux:text class="font-medium">{{ $order->code_order ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">SR Code</flux:text>
                <flux:text class="font-medium">{{ $order->code_request }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $order->partner?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Category</flux:text>
                <flux:text class="font-medium">{{ $order->itemCategory?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">WO Auto</flux:text>
                <flux:text class="font-medium">{{ $order->work_order_auto ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">WO Manual</flux:text>
                <flux:text class="font-medium">{{ $order->work_order_manual ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Production Date</flux:text>
                <flux:text class="font-medium">{{ $order->production_date?->format('d M Y') ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">SO Status</flux:text>
                <flux:text class="font-medium">{{ $order->status }}</flux:text>
            </div>
        </div>
    </flux:card>

    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Production Status</flux:heading>

        <form wire:submit="save" class="space-y-4">
            <flux:select wire:model="production_status" label="Production Status" badge="Required">
                <flux:select.option value="ongoing">Ongoing</flux:select.option>
                <flux:select.option value="finish">Finish</flux:select.option>
            </flux:select>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button :href="route('production.index')" variant="ghost" wire:navigate>Cancel</flux:button>
                @can('edit production order')
                    <flux:button type="submit" variant="primary">Save</flux:button>
                @endcan
            </div>
        </form>
    </flux:card>
</div>
