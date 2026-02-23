<div>
    <div class="mb-6">
        @if($order->status === 'REJECTED')
            <flux:button :href="route('sales.order.index.rejected')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Rejected Orders
            </flux:button>
        @else
            <flux:button :href="route('sales.order.index.ongoing')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Ongoing Orders
            </flux:button>
        @endif
    </div>

    <flux:heading size="xl" class="mb-2">Sales Order Detail</flux:heading>
    <flux:subheading class="mb-6">
        @if($order->code_order)
            {{ $order->code_order }} ({{ $order->code_request }})
        @else
            {{ $order->code_request }}
        @endif
    </flux:subheading>

    {{-- Order Information --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Order Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">SR Code</flux:text>
                <flux:text class="font-medium">{{ $order->code_request }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">SO Code</flux:text>
                <flux:text class="font-medium">{{ $order->code_order ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                @if($order->status === 'ORDER')
                    <flux:badge color="green">{{ $order->status }}</flux:badge>
                @elseif($order->status === 'REJECTED')
                    <flux:badge color="red">{{ $order->status }}</flux:badge>
                @else
                    <flux:badge>{{ $order->status }}</flux:badge>
                @endif
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $order->date?->format('d M Y') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Delivery Date</flux:text>
                <flux:text class="font-medium">{{ $order->delivery_date?->format('d M Y') ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $order->partner?->name }} ({{ $order->partner?->code }})</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Company</flux:text>
                <flux:text class="font-medium">{{ $order->company?->name }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Item Category</flux:text>
                <flux:text class="font-medium">{{ $order->itemCategory?->name }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                <flux:text class="font-medium">{{ $order->currency?->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Requested By</flux:text>
                <flux:text class="font-medium">{{ $order->createdBy?->name }}</flux:text>
            </div>
            @if($order->approvedByUser)
            <div>
                <flux:text class="text-sm text-zinc-500">Approved By</flux:text>
                <flux:text class="font-medium">{{ $order->approvedByUser?->name }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Approved At</flux:text>
                <flux:text class="font-medium">{{ $order->approved_at?->format('d M Y H:i') }}</flux:text>
            </div>
            @endif
            @if($order->rejection_reason)
                <div class="md:col-span-3">
                    <flux:text class="text-sm text-zinc-500">Rejection Reason</flux:text>
                    <flux:text class="font-medium text-red-600 dark:text-red-400 whitespace-pre-line">{{ $order->rejection_reason }}</flux:text>
                </div>
            @endif
            @if($order->remarks)
                <div class="md:col-span-3">
                    <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                    <flux:text class="font-medium whitespace-pre-line">{{ $order->remarks }}</flux:text>
                </div>
            @endif
        </div>
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
                    <flux:table.column>UOM</flux:table.column>
                    <flux:table.column class="text-center">Quantity</flux:table.column>
                    <flux:table.column class="text-center">Price Proposed</flux:table.column>
                    <flux:table.column class="text-center">Price Deal</flux:table.column>
                    <flux:table.column class="text-center">Discount</flux:table.column>
                    <flux:table.column class="text-center">Tax</flux:table.column>
                    <flux:table.column class="text-center">Total</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($order->details as $index => $detail)
                        <flux:table.row :key="'detail-'.$detail->id">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->item?->code }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->item?->name }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->itemUom?->uom?->name }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->price_proposed, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->price_deal, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->discount, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->tax, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format((float)$detail->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-3 border-t border-zinc-200 dark:border-zinc-700 pt-3 flex justify-end text-sm">
                <div class="flex gap-8 font-semibold">
                    <span>Grand Total:</span>
                    <span class="tabular-nums min-w-[120px] text-right">{{ number_format((float)$order->total, 2) }}</span>
                </div>
            </div>
        @else
            <div class="text-center py-8 text-zinc-400">
                <flux:text>No items in this order.</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Actions for REJECTED orders --}}
    @if($order->status === 'REJECTED')
        @can('create sales request')
        <flux:card>
            <flux:heading size="lg" class="mb-4">Actions</flux:heading>

            <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
                This order was rejected. You can replicate it as a new Sales Request (draft) to revise and resubmit.
            </flux:text>

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.trigger name="confirm-replicate">
                    <flux:button variant="primary" icon="document-duplicate">
                        Replicate to New Request
                    </flux:button>
                </flux:modal.trigger>
            </div>

            <flux:modal name="confirm-replicate" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Replicate to New Request?</flux:heading>
                        <flux:text class="mt-2">
                            This will create a new Sales Request from this rejected order. Continue?
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer/>
                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>
                        <flux:button wire:click="replicateToRequest" variant="primary" icon="document-duplicate">
                            Replicate
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:card>
        @endcan
    @endif
</div>
