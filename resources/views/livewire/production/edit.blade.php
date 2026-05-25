<div>
    <div class="mb-6">
        <flux:button :href="route('production.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Production
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Edit Production</flux:heading>

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
                <flux:text class="text-sm text-zinc-500">SO Status</flux:text>
                <flux:text class="font-medium">{{ $order->status }}</flux:text>
            </div>
        </div>
    </flux:card>

    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Production</flux:heading>

        @if($readOnly)
            <flux:callout color="zinc" icon="lock-closed" class="mb-4">
                <flux:callout.heading>Production Finished</flux:callout.heading>
                <flux:callout.text>WO Manual, Production Date, and Production Status are no longer editable.</flux:callout.text>
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    wire:model="work_order_manual"
                    label="WO Manual"
                    placeholder="Enter manual work order number"
                    maxlength="100"
                    :disabled="$readOnly" />

                <flux:date-picker
                    wire:model="production_date"
                    label="Production Date"
                    clearable
                    :disabled="$readOnly" />
            </div>

            <flux:select wire:model="production_status" label="Production Status" badge="Required" :disabled="$readOnly">
                <flux:select.option value="ongoing">Ongoing</flux:select.option>
                <flux:select.option value="finish">Finish</flux:select.option>
            </flux:select>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button :href="route('production.index')" variant="ghost" wire:navigate>Cancel</flux:button>
                @can('edit production order')
                    <flux:button type="submit" variant="primary" :disabled="$readOnly">Save</flux:button>
                @endcan
            </div>
        </form>
    </flux:card>

    {{-- Items --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Items</flux:heading>

        @if($order->details->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Code</flux:table.column>
                    <flux:table.column>Item Name</flux:table.column>
                    <flux:table.column class="text-center">Quantity</flux:table.column>
                    <flux:table.column>UOM</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($order->details as $index => $detail)
                        <flux:table.row :key="'detail-'.$detail->id">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->item?->code }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->item?->name }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->itemUom?->uom?->name }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="text-center py-8 text-zinc-400">
                <flux:text>No items in this order.</flux:text>
            </div>
        @endif
    </flux:card>
</div>
