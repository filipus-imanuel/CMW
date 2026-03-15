<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('warehouses.delivery.ongoing.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Ongoing Deliveries
        </flux:button>
    </div>

    <div class="flex items-center justify-between mb-2">
        <flux:heading size="xl">Delivery Order Detail</flux:heading>

        @if($header->isOngoing())
            <div class="flex gap-2">
                @can('view delivery order')
                <flux:button
                    variant="primary"
                    icon="arrow-down-tray"
                    href="{{ route('warehouses.delivery.pdf', $header->id) }}"
                    target="_blank"
                >
                    Download Faktur
                </flux:button>
                <flux:button
                    variant="primary"
                    icon="document-text"
                    href="{{ route('warehouses.delivery.surat-jalan', $header->id) }}"
                    target="_blank"
                >
                    Download Surat Jalan
                </flux:button>
                @endcan

                @can('confirm delivery order')
                <flux:button
                    variant="primary"
                    icon="check-circle"
                    x-on:click="$flux.modal('confirm-receipt').show()"
                >
                    Confirm Receipt
                </flux:button>
                @endcan

                <flux:button
                    variant="ghost"
                    icon="arrow-path"
                    x-on:click="$flux.modal('resend-request').show()"
                >
                    Re-send Request
                </flux:button>

                @can('force finish delivery order')
                <flux:button
                    variant="danger"
                    icon="forward"
                    x-on:click="$flux.modal('force-finish').show()"
                >
                    Force Finish
                </flux:button>
                @endcan

                @can('cancel delivery order')
                <flux:button
                    variant="danger"
                    icon="x-circle"
                    x-on:click="$flux.modal('cancel-delivery').show()"
                >
                    Cancel
                </flux:button>
                @endcan
            </div>
        @endif
    </div>

    {{-- Confirm Receipt Modal --}}
    <flux:modal name="confirm-receipt" class="max-w-2xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Confirm Receipt</flux:heading>
                <flux:text class="mt-2 text-zinc-400">
                    Confirm the received quantities. An AR Invoice will be automatically generated.
                </flux:text>
            </div>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Item</flux:table.column>
                        <flux:table.column>UOM</flux:table.column>
                        <flux:table.column class="text-center">Qty Sent</flux:table.column>
                        <flux:table.column class="text-center">Qty Received</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($header->details as $detail)
                            <flux:table.row>
                                <flux:table.cell>{{ $detail->item?->name }}</flux:table.cell>
                                <flux:table.cell>{{ $detail->itemUom?->uom?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_sent, 2) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:input
                                        wire:model="receivedQuantities.{{ $detail->id }}"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        :max="(float)$detail->quantity_sent"
                                        size="sm"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('confirm-receipt').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="confirmReceipt" x-on:click="$flux.modal('confirm-receipt').close()">Confirm &amp; Create Invoice</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Re-send Request Modal --}}
    <flux:modal name="resend-request" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Re-send Request</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Add a note for the re-send request. No status change will occur.</flux:text>
            </div>

            <flux:textarea
                wire:model="resendNote"
                label="Note"
                rows="3"
                placeholder="Reason for re-send request (optional)"
                maxlength="1024"
            />

            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('resend-request').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="resendRequest" x-on:click="$flux.modal('resend-request').close()">Submit Request</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Force Finish Modal --}}
    <flux:modal name="force-finish" class="max-w-2xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Force Finish Delivery</flux:heading>
                <flux:text class="mt-2 text-zinc-400">
                    This will mark the delivery as finished and set the SO status to FINISH regardless of remaining quantities.
                    An AR Invoice will be generated. You can adjust received quantities below.
                </flux:text>
            </div>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Item</flux:table.column>
                        <flux:table.column>UOM</flux:table.column>
                        <flux:table.column class="text-center">Qty Sent</flux:table.column>
                        <flux:table.column class="text-center">Qty Received</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($header->details as $detail)
                            <flux:table.row>
                                <flux:table.cell>{{ $detail->item?->name }}</flux:table.cell>
                                <flux:table.cell>{{ $detail->itemUom?->uom?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_sent, 2) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:input
                                        wire:model="receivedQuantities.{{ $detail->id }}"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        :max="(float)$detail->quantity_sent"
                                        size="sm"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('force-finish').close()">Back</flux:button>
                <flux:button variant="danger" wire:click="forceFinish" x-on:click="$flux.modal('force-finish').close()">Force Finish &amp; Invoice</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Cancel Delivery Modal --}}
    <flux:modal name="cancel-delivery" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Cancel Delivery Order</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Stock will be returned to inventory. A reason is required.</flux:text>
            </div>

            <flux:textarea
                wire:model="cancelReason"
                label="Cancel Reason"
                badge="Required"
                rows="3"
                placeholder="Enter the reason for cancellation"
                maxlength="1024"
            />

            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('cancel-delivery').close()">Back</flux:button>
                <flux:button variant="danger" wire:click="cancelDelivery" x-on:click="$flux.modal('cancel-delivery').close()">Cancel Delivery</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:subheading class="mb-6">{{ $header->code }}</flux:subheading>

    {{-- Header Information --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Delivery Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">DO Code</flux:text>
                <flux:text class="font-medium">{{ $header->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">SO Code</flux:text>
                <flux:text class="font-medium">
                    <a href="{{ route('sales.order.show', $header->order_header_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline" wire:navigate>
                        {{ $header->orderHeader?->code_order ?? $header->orderHeader?->code_request }}
                    </a>
                </flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                @if($header->isOngoing())
                    <flux:badge color="blue">Ongoing</flux:badge>
                @elseif($header->isFinished())
                    <flux:badge color="green">Finished</flux:badge>
                @elseif($header->isCancelled())
                    <flux:badge color="red">Cancelled</flux:badge>
                @endif
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Delivery Date</flux:text>
                <flux:text class="font-medium">{{ $header->date?->format('d M Y') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $header->partner?->name }} ({{ $header->partner?->code }})</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Company</flux:text>
                <flux:text class="font-medium">{{ $header->company?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                <flux:text class="font-medium">{{ $header->currency?->code ?? '-' }}</flux:text>
            </div>
            @if($header->vehicle_number)
                <div>
                    <flux:text class="text-sm text-zinc-500">Vehicle Number</flux:text>
                    <flux:text class="font-medium">{{ $header->vehicle_number }}</flux:text>
                </div>
            @endif
            <div>
                <flux:text class="text-sm text-zinc-500">Created By</flux:text>
                <flux:text class="font-medium">{{ $header->createdBy?->name ?? '-' }}</flux:text>
            </div>
            @if($header->confirmedByUser)
                <div>
                    <flux:text class="text-sm text-zinc-500">Confirmed By</flux:text>
                    <flux:text class="font-medium">{{ $header->confirmedByUser?->name }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Confirmed At</flux:text>
                    <flux:text class="font-medium">{{ $header->confirmed_at?->format('d M Y H:i') }}</flux:text>
                </div>
            @endif
        </div>

        @if($header->delivery_address)
            <div class="mt-4">
                <flux:text class="text-sm text-zinc-500">Delivery Address</flux:text>
                <flux:text class="whitespace-pre-line">{{ $header->delivery_address }}</flux:text>
            </div>
        @endif

        @if($header->remarks)
            <div class="mt-4">
                <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                <flux:text class="whitespace-pre-line">{{ $header->remarks }}</flux:text>
            </div>
        @endif

        @if($header->cancel_reason)
            <div class="mt-4">
                <flux:text class="text-sm text-zinc-500">Cancel Reason</flux:text>
                <flux:text class="font-medium text-red-600 dark:text-red-400 whitespace-pre-line">{{ $header->cancel_reason }}</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Summary --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Totals</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Subtotal</flux:text>
                <flux:text class="font-medium tabular-nums">{{ number_format((float)$header->subtotal, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Tax</flux:text>
                <flux:text class="font-medium tabular-nums">{{ number_format((float)$header->tax, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Total</flux:text>
                <flux:text class="font-medium tabular-nums text-lg">{{ number_format((float)$header->total, 2) }}</flux:text>
            </div>
        </div>
    </flux:card>

    {{-- Delivery Lines --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Delivery Lines</flux:heading>

        @if($header->details->count() > 0)
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>Item</flux:table.column>
                        <flux:table.column>UOM</flux:table.column>
                        <flux:table.column>Warehouse</flux:table.column>
                        <flux:table.column class="text-center">Qty Sent</flux:table.column>
                        <flux:table.column class="text-center">Qty Received</flux:table.column>
                        <flux:table.column class="text-center">Price</flux:table.column>
                        <flux:table.column class="text-center">Discount</flux:table.column>
                        <flux:table.column class="text-center">Tax</flux:table.column>
                        <flux:table.column class="text-center">Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($header->details as $i => $detail)
                            <flux:table.row>
                                <flux:table.cell>{{ $i + 1 }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($detail->item)
                                        <flux:text class="font-medium">{{ $detail->item->code }}</flux:text>
                                        <flux:text class="text-sm text-zinc-500">{{ $detail->item->name }}</flux:text>
                                    @else
                                        <flux:text>-</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $detail->itemUom?->uom?->code ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $detail->warehouse?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_sent, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    @if($header->isFinished())
                                        {{ number_format((float)$detail->quantity_received, 2) }}
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->price, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->discount, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->tax, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format((float)$detail->total, 2) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <flux:callout variant="info" icon="information-circle">
                No line items found.
            </flux:callout>
        @endif
    </flux:card>

    {{-- AR Invoices --}}
    @if($header->arInvoices->count() > 0)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">AR Invoices</flux:heading>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>Invoice Code</flux:table.column>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Due Date</flux:table.column>
                        <flux:table.column class="text-center">Total</flux:table.column>
                        <flux:table.column class="text-center">Paid</flux:table.column>
                        <flux:table.column class="text-center">Balance</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($header->arInvoices as $j => $invoice)
                            <flux:table.row>
                                <flux:table.cell>{{ $j + 1 }}</flux:table.cell>
                                <flux:table.cell class="font-medium">{{ $invoice->code }}</flux:table.cell>
                                <flux:table.cell>{{ $invoice->date?->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $invoice->due_date?->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$invoice->total, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$invoice->paid, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$invoice->balance, 2) }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($invoice->status === 'unpaid')
                                        <flux:badge color="red">Unpaid</flux:badge>
                                    @elseif($invoice->status === 'partial')
                                        <flux:badge color="yellow">Partial</flux:badge>
                                    @elseif($invoice->status === 'paid')
                                        <flux:badge color="green">Paid</flux:badge>
                                    @else
                                        <flux:badge>{{ $invoice->status }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    @endif

    {{-- Inventory Ledger Entries --}}
    @if($header->inventoryLedgers->count() > 0)
        <flux:card>
            <flux:heading size="lg" class="mb-4">Inventory Ledger Entries (Audit Trail)</flux:heading>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column class="text-center">Qty In</flux:table.column>
                        <flux:table.column class="text-center">Qty Out</flux:table.column>
                        <flux:table.column class="text-center">Balance</flux:table.column>
                        <flux:table.column class="text-center">Unit Cost</flux:table.column>
                        <flux:table.column>Remarks</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($header->inventoryLedgers as $j => $ledger)
                            <flux:table.row>
                                <flux:table.cell>{{ $j + 1 }}</flux:table.cell>
                                <flux:table.cell>{{ $ledger->date?->format('d M Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-800">
                                        {{ ucfirst($ledger->type) }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums text-green-600">
                                    {{ number_format($ledger->quantity_in, 2) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums text-red-600">
                                    {{ number_format($ledger->quantity_out, 2) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    {{ number_format($ledger->balance, 2) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    {{ number_format($ledger->unit_cost, 2) }}
                                </flux:table.cell>
                                <flux:table.cell class="max-w-xs truncate">{{ $ledger->remarks ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    @endif
</div>
