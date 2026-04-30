<div>
    <div class="mb-6">
        @if($order->status === 'INIT')
            <flux:button :href="route('sales.request.index.init')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Sales Requests
            </flux:button>
        @elseif($order->status === 'REJECTED')
            <flux:button :href="route('sales.order.index.rejected')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Rejected Orders
            </flux:button>
        @elseif($order->status === 'CANCELLED')
            <flux:button :href="route('sales.order.index.cancelled')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Cancelled Orders
            </flux:button>
        @else
            <flux:button :href="route('sales.order.index.ongoing')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Ongoing Orders
            </flux:button>
        @endif
    </div>

    <flux:heading size="xl" class="mb-2">Sales Order Detail</flux:heading>
    <div class="flex items-center justify-between mb-6">
        <flux:subheading>
            @if($order->code_order)
                {{ $order->code_order }} ({{ $order->code_request }})
            @else
                {{ $order->code_request }}
            @endif
        </flux:subheading>

        @if($order->code_order)
            @can('view sales order')
                <flux:button variant="primary" size="sm" icon="arrow-down-tray"
                    href="{{ route('sales.order.pdf', $order->id) }}" target="_blank">
                    Download PDF
                </flux:button>
            @endcan
        @endif
    </div>

    {{-- Order Information --}}
    <flux:card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Order Information</flux:heading>
            @if(in_array($order->status, ['INIT', 'ORDER']) && !$order->deliveries->count())
                @can('edit sales order')
                    <flux:button variant="danger" size="sm" icon="no-symbol"
                        x-on:click="$flux.modal('cancel-order-confirmation').show()">
                        Cancel Order
                    </flux:button>
                @endcan
            @endif
        </div>

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
                @if($order->status === 'INIT')
                    <flux:badge color="zinc">DRAFT</flux:badge>
                @elseif($order->status === 'ORDER')
                    <flux:badge color="green">{{ $order->status }}</flux:badge>
                @elseif($order->status === 'DELIVERY')
                    <flux:badge color="blue">{{ $order->status }}</flux:badge>
                @elseif($order->status === 'FINISH')
                    <flux:badge color="emerald">{{ $order->status }}</flux:badge>
                @elseif($order->status === 'REJECTED')
                    <flux:badge color="red">{{ $order->status }}</flux:badge>
                @elseif($order->status === 'CANCELLED')
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

    {{-- Work Order --}}
    @if($order->work_order_auto)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Work Order</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:text class="text-sm text-zinc-500">WO Auto</flux:text>
                    <flux:text class="font-medium">{{ $order->work_order_auto }}</flux:text>
                </div>

                @if(in_array($order->status, ['ORDER', 'DELIVERY']))
                    @can('edit sales order')
                        <div class="flex items-end gap-2">
                            <flux:input
                                wire:model="work_order_manual"
                                label="WO Manual"
                                placeholder="Enter manual work order number"
                                maxlength="100"
                                class="flex-1" />
                            <flux:button wire:click="saveWorkOrderManual" variant="primary" icon="check">
                                Save
                            </flux:button>
                        </div>
                    @else
                        <div>
                            <flux:text class="text-sm text-zinc-500">WO Manual</flux:text>
                            <flux:text class="font-medium">{{ $order->work_order_manual ?? '-' }}</flux:text>
                        </div>
                    @endcan
                @else
                    <div>
                        <flux:text class="text-sm text-zinc-500">WO Manual</flux:text>
                        <flux:text class="font-medium">{{ $order->work_order_manual ?? '-' }}</flux:text>
                    </div>
                @endif
            </div>
        </flux:card>
    @endif

    {{-- Cancellation / Rejection Reason Callout --}}
    @if($order->status === 'CANCELLED' && $order->rejection_reason)
        <flux:callout color="red" icon="no-symbol" class="mb-6">
            <flux:callout.heading>Cancellation Reason</flux:callout.heading>
            <flux:callout.text>{{ $order->rejection_reason }}</flux:callout.text>
        </flux:callout>
    @elseif($order->status === 'REJECTED' && $order->rejection_reason)
        <flux:callout color="red" icon="x-circle" class="mb-6">
            <flux:callout.heading>Rejection Reason</flux:callout.heading>
            <flux:callout.text>{{ $order->rejection_reason }}</flux:callout.text>
        </flux:callout>
    @endif

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
                <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-right font-semibold">
                    <span>Subtotal:</span>
                    <span class="tabular-nums">{{ number_format((float)$order->subtotal, 2) }}</span>
                    <span>Discount:</span>
                    <span class="tabular-nums">{{ number_format((float)$order->discount, 2) }}</span>
                    <span>Tax:</span>
                    <span class="tabular-nums">{{ number_format((float)$order->tax, 2) }}</span>
                    <span class="text-lg">Grand Total:</span>
                    <span class="tabular-nums text-lg">{{ number_format((float)$order->total, 2) }}</span>
                    @php
                        $activeReturnTotal = $order->returns?->whereNotIn('status', ['CANCELLED', 'REJECTED'])->sum('total') ?? 0;
                    @endphp
                    @if($activeReturnTotal > 0)
                        <span class="text-red-500">Return:</span>
                        <span class="tabular-nums text-red-500">({{ number_format((float)$activeReturnTotal, 2) }})</span>
                        <span class="text-lg">Net Total:</span>
                        <span class="tabular-nums text-lg">{{ number_format((float)$order->total - (float)$activeReturnTotal, 2) }}</span>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-8 text-zinc-400">
                <flux:text>No items in this order.</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Delivery Schedule (read-only) --}}
    <x-sales.delivery-schedule-readonly :order="$order" />

    {{-- Delivery Orders --}}
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
                        <flux:table.column>Return</flux:table.column>
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
                                <flux:table.cell>
                                    @if($delivery->status === 'finished')
                                        @php
                                            $hasActiveReturn = $delivery->returns?->whereNotIn('status', ['CANCELLED', 'REJECTED'])->isNotEmpty();
                                        @endphp
                                        @if($hasActiveReturn)
                                            <flux:badge color="amber" size="sm">Return Active</flux:badge>
                                        @else
                                            @can('create sales return')
                                                <flux:button variant="subtle" size="xs" :href="route('sales.return.create', ['deliveryId' => $delivery->id])" wire:navigate icon="arrow-uturn-left">
                                                    Create Return
                                                </flux:button>
                                            @endcan
                                        @endif
                                    @else
                                        <flux:text class="text-zinc-400">-</flux:text>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    @endif

    {{-- Sales Returns --}}
    @if($order->returns && $order->returns->count() > 0)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Sales Returns</flux:heading>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>RTN Code</flux:table.column>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>DO Code</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-center">Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($order->returns as $ri => $return)
                            <flux:table.row>
                                <flux:table.cell>{{ $ri + 1 }}</flux:table.cell>
                                <flux:table.cell>
                                    <a href="{{ route('sales.return.show', $return->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $return->code }}</a>
                                </flux:table.cell>
                                <flux:table.cell>{{ $return->date?->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $return->deliveryHeader?->code ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="\App\Models\CMW\Transaction\ReturnHeader::returnTypeBadgeColor($return->return_type)" size="sm">{{ $return->return_type }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $rtnStatusColor = match($return->status) {
                                            'INIT' => 'zinc',
                                            'APPROVAL' => 'amber',
                                            'PROCESSING' => 'blue',
                                            'FINISH' => 'green',
                                            'REJECTED' => 'red',
                                            'CANCELLED' => 'zinc',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge :color="$rtnStatusColor" size="sm">{{ $return->status }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$return->total, 2) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    @endif

    {{-- Actions for REJECTED or CANCELLED orders --}}
    @if(in_array($order->status, ['REJECTED', 'CANCELLED']))
        @can('create sales request')
        <flux:card>
            <flux:heading size="lg" class="mb-4">Actions</flux:heading>

            <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
                This order was {{ strtolower($order->status) }}. You can replicate it as a new Sales Request (draft) to revise and resubmit.
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
                            This will create a new Sales Request from this {{ strtolower($order->status) }} order. Continue?
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

    {{-- Cancel Order Confirmation Modal --}}
    <flux:modal name="cancel-order-confirmation" class="max-w-sm">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Cancel Order</flux:heading>
                <flux:text class="mt-2">Please provide a reason for cancelling this order.</flux:text>
            </div>
            <flux:textarea wire:model="cancellation_reason" label="Cancellation Reason" placeholder="Enter reason for cancellation..." rows="3" required />
            <div class="flex gap-2">
                <flux:spacer/>
                <flux:button variant="ghost" x-on:click="$flux.modal('cancel-order-confirmation').close()">Back</flux:button>
                <flux:button variant="danger" icon="no-symbol" wire:click="cancelOrder" x-on:click="$flux.modal('cancel-order-confirmation').close()">Cancel Order</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
