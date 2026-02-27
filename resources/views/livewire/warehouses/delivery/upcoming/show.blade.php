<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('warehouses.delivery.upcoming')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Upcoming Deliveries
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Sales Order — Delivery</flux:heading>
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
                @elseif($order->status === 'DELIVERY')
                    <flux:badge color="blue">{{ $order->status }}</flux:badge>
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
                <flux:text class="font-medium">{{ $order->company?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Item Category</flux:text>
                <flux:text class="font-medium">{{ $order->itemCategory?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                <flux:text class="font-medium">{{ $order->currency?->code ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Requested By</flux:text>
                <flux:text class="font-medium">{{ $order->createdBy?->name ?? '-' }}</flux:text>
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
            @if($order->remarks)
                <div class="md:col-span-3">
                    <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                    <flux:text class="font-medium whitespace-pre-line">{{ $order->remarks }}</flux:text>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Items with delivery progress --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Items — Delivery Progress</flux:heading>

        @if(count($lines) > 0)
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>Code</flux:table.column>
                        <flux:table.column>Item Name</flux:table.column>
                        <flux:table.column>UOM</flux:table.column>
                        <flux:table.column class="text-center">Ordered</flux:table.column>
                        <flux:table.column class="text-center">Delivered</flux:table.column>
                        <flux:table.column class="text-center">Remaining</flux:table.column>
                        <flux:table.column class="text-center">Price</flux:table.column>
                        <flux:table.column class="text-center">Discount</flux:table.column>
                        <flux:table.column class="text-center">Tax</flux:table.column>
                        <flux:table.column class="text-center">Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($lines as $i => $line)
                            <flux:table.row :key="'line-'.$i">
                                <flux:table.cell>{{ $i + 1 }}</flux:table.cell>
                                <flux:table.cell>{{ $line['item_code'] }}</flux:table.cell>
                                <flux:table.cell>{{ $line['item_name'] }}</flux:table.cell>
                                <flux:table.cell>{{ $line['uom_name'] }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format($line['quantity_ordered'], 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    <span class="{{ $line['quantity_delivered'] > 0 ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                                        {{ number_format($line['quantity_delivered'], 2) }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    <span class="{{ $line['quantity_remaining'] > 0 ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($line['quantity_remaining'], 2) }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format($line['price'], 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format($line['discount'], 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format($line['tax'], 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format($line['total'], 2) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

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

    {{-- Existing Delivery Orders --}}
    @if($order->deliveries && $order->deliveries->count() > 0)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Delivery Orders</flux:heading>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>DO Code</flux:table.column>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-center">Total</flux:table.column>
                        <flux:table.column>Created By</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($order->deliveries as $di => $delivery)
                            <flux:table.row>
                                <flux:table.cell>{{ $di + 1 }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($delivery->status === 'ongoing')
                                        <a href="{{ route('warehouses.delivery.ongoing.show', $delivery->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $delivery->code }}</a>
                                    @elseif($delivery->status === 'finished')
                                        <a href="{{ route('warehouses.delivery.finish.show', $delivery->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $delivery->code }}</a>
                                    @elseif($delivery->status === 'cancelled')
                                        <a href="{{ route('warehouses.delivery.cancelled.show', $delivery->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $delivery->code }}</a>
                                    @else
                                        <flux:text class="font-medium">{{ $delivery->code }}</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $delivery->date?->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($delivery->status === 'ongoing')
                                        <flux:badge color="blue">Ongoing</flux:badge>
                                    @elseif($delivery->status === 'finished')
                                        <flux:badge color="green">Finished</flux:badge>
                                    @elseif($delivery->status === 'cancelled')
                                        <flux:badge color="red">Cancelled</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$delivery->total, 2) }}</flux:table.cell>
                                <flux:table.cell>{{ $delivery->createdBy?->name ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    @endif

    {{-- Create DO Action --}}
    @can('create delivery order')
        @php
            $hasRemaining = collect($lines)->contains(fn ($line) => $line['quantity_remaining'] > 0);
        @endphp
        @if($hasRemaining)
            <div class="flex gap-2">
                <flux:spacer />
                <flux:button :href="route('warehouses.delivery.create', ['orderId' => $order->id])" variant="primary" icon="paper-airplane" wire:navigate>
                    Create Delivery Order
                </flux:button>
            </div>
        @else
            <flux:callout variant="success" icon="check-circle">
                All items have been fully delivered for this sales order.
            </flux:callout>
        @endif
    @endcan
</div>
