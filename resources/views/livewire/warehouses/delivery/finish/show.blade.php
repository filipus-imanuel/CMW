<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('warehouses.delivery.finish.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Finished Deliveries
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Finished Delivery Detail</flux:heading>
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
                <flux:badge color="green">Finished</flux:badge>
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
            <div>
                <flux:text class="text-sm text-zinc-500">Created By</flux:text>
                <flux:text class="font-medium">{{ $header->createdBy?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Confirmed By</flux:text>
                <flux:text class="font-medium">{{ $header->confirmedByUser?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Confirmed At</flux:text>
                <flux:text class="font-medium">{{ $header->confirmed_at?->format('d M Y H:i') ?? '-' }}</flux:text>
            </div>
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
                                <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_received, 2) }}</flux:table.cell>
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

    {{-- Sales Returns --}}
    @if($header->returns->count() > 0)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Sales Returns</flux:heading>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>RTN Code</flux:table.column>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-center">Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($header->returns as $ri => $return)
                            <flux:table.row>
                                <flux:table.cell>{{ $ri + 1 }}</flux:table.cell>
                                <flux:table.cell>
                                    <a href="{{ route('sales.return.show', $return->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $return->code }}</a>
                                </flux:table.cell>
                                <flux:table.cell>{{ $return->date?->format('d M Y') }}</flux:table.cell>
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
                                            'CANCELLED' => 'red',
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
