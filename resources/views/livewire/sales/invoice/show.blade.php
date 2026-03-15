<div>
    <div class="mb-6">
        @if($invoice->isPaid())
            <flux:button :href="route('sales.invoice.index.paid')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Paid Invoices
            </flux:button>
        @else
            <flux:button :href="route('sales.invoice.index.unpaid')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Unpaid Invoices
            </flux:button>
        @endif
    </div>

    <flux:heading size="xl" class="mb-2">Invoice Detail</flux:heading>
    <flux:subheading class="mb-6">{{ $invoice->code }}</flux:subheading>

    {{-- Invoice Information --}}
    <flux:card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Invoice Information</flux:heading>
            @if($invoice->hasOutstandingBalance())
                @can('create ar payment')
                    <flux:button variant="primary" size="sm" icon="banknotes"
                        :href="route('sales.payment.create', ['invoiceId' => $invoice->id])" wire:navigate>
                        Record Payment
                    </flux:button>
                @endcan
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Invoice Code</flux:text>
                <flux:text class="font-medium">{{ $invoice->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $invoice->date?->format('d M Y') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Due Date</flux:text>
                <flux:text class="font-medium">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $invoice->partner?->name }} ({{ $invoice->partner?->code }})</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Company</flux:text>
                <flux:text class="font-medium">{{ $invoice->orderHeader?->company?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                <flux:text class="font-medium">{{ $invoice->currency?->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">SO Code</flux:text>
                <flux:text class="font-medium">
                    @if($invoice->orderHeader)
                        <a href="{{ route('sales.order.show', $invoice->orderHeader->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline" wire:navigate>{{ $invoice->orderHeader->code_order }}</a>
                    @else
                        -
                    @endif
                </flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">DO Code</flux:text>
                <flux:text class="font-medium">{{ $invoice->deliveryHeader?->code ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                @if($invoice->isUnpaid())
                    <flux:badge color="amber">UNPAID</flux:badge>
                @elseif($invoice->isPartial())
                    <flux:badge color="blue">PARTIAL</flux:badge>
                @elseif($invoice->isPaid())
                    <flux:badge color="green">PAID</flux:badge>
                @else
                    <flux:badge>{{ strtoupper($invoice->status) }}</flux:badge>
                @endif
            </div>
            @if($invoice->remarks)
                <div class="md:col-span-3">
                    <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                    <flux:text class="font-medium whitespace-pre-line">{{ $invoice->remarks }}</flux:text>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Financial Summary --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Financial Summary</flux:heading>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Subtotal</flux:text>
                <flux:text class="font-semibold tabular-nums">{{ number_format((float) $invoice->subtotal, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Tax</flux:text>
                <flux:text class="font-semibold tabular-nums">{{ number_format((float) $invoice->tax, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Total</flux:text>
                <flux:text class="font-semibold tabular-nums text-lg">{{ number_format((float) $invoice->total, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Paid</flux:text>
                <flux:text class="font-semibold tabular-nums text-green-600 dark:text-green-400">{{ number_format((float) $invoice->paid, 2) }}</flux:text>
            </div>
        </div>

        @if($invoice->hasOutstandingBalance())
            <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between items-center">
                    <flux:text class="font-semibold text-lg">Outstanding Balance</flux:text>
                    <flux:text class="font-bold tabular-nums text-lg text-red-600 dark:text-red-400">{{ number_format((float) $invoice->balance, 2) }}</flux:text>
                </div>
            </div>
        @endif
    </flux:card>

    {{-- Delivery Items --}}
    @if($invoice->deliveryHeader && $invoice->deliveryHeader->details->count() > 0)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Delivery Items</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Code</flux:table.column>
                    <flux:table.column>Item Name</flux:table.column>
                    <flux:table.column>UOM</flux:table.column>
                    <flux:table.column class="text-center">Qty Sent</flux:table.column>
                    <flux:table.column class="text-center">Qty Received</flux:table.column>
                    <flux:table.column class="text-center">Price</flux:table.column>
                    <flux:table.column class="text-center">Discount</flux:table.column>
                    <flux:table.column class="text-center">Tax</flux:table.column>
                    <flux:table.column class="text-center">Total</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($invoice->deliveryHeader->details as $index => $detail)
                        <flux:table.row :key="'delivery-detail-'.$detail->id">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->item?->code }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->item?->name }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->itemUom?->uom?->name }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $detail->quantity_sent, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $detail->quantity_received, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $detail->price, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $detail->discount, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $detail->tax, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format((float) $detail->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    {{-- Payment History --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Payment History</flux:heading>

        @if($invoice->paymentDetails->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Payment Code</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Method</flux:table.column>
                    <flux:table.column class="text-center">Amount</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($invoice->paymentDetails as $index => $paymentDetail)
                        <flux:table.row :key="'payment-'.$paymentDetail->id">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>
                                <a href="{{ route('sales.payment.show', $paymentDetail->header->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $paymentDetail->header->code }}</a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $paymentDetail->header->date?->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $paymentDetail->header->paymentMethod?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $paymentDetail->amount, 2) }}</flux:table.cell>
                            <flux:table.cell>
                                @if($paymentDetail->header->status === 'active')
                                    <flux:badge color="green" size="sm">ACTIVE</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">CANCELLED</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @can('view ar payment')
                                    <flux:button variant="subtle" size="xs" :href="route('sales.payment.show', $paymentDetail->header->id)" wire:navigate icon="eye">
                                        View
                                    </flux:button>
                                @endcan
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="text-center py-8 text-zinc-400">
                <flux:text>No payments recorded yet.</flux:text>
            </div>
        @endif
    </flux:card>
</div>
