<div>
    <div class="mb-6">
        @if($payment->isActive())
            <flux:button :href="route('sales.payment.index.active')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Active Payments
            </flux:button>
        @else
            <flux:button :href="route('sales.payment.index.cancelled')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to Cancelled Payments
            </flux:button>
        @endif
    </div>

    <flux:heading size="xl" class="mb-2">Payment Detail</flux:heading>
    <flux:subheading class="mb-6">{{ $payment->code }}</flux:subheading>

    {{-- Cancellation Callout --}}
    @if($payment->isCancelled() && $payment->cancel_reason)
        <flux:callout color="red" icon="no-symbol" class="mb-6">
            <flux:callout.heading>Cancellation Reason</flux:callout.heading>
            <flux:callout.text>{{ $payment->cancel_reason }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Payment Information --}}
    <flux:card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Payment Information</flux:heading>
            @if($payment->isActive())
                @can('cancel ar payment')
                    <flux:button variant="danger" size="sm" icon="no-symbol"
                        x-on:click="$flux.modal('cancel-payment-confirmation').show()">
                        Cancel Payment
                    </flux:button>
                @endcan
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Payment Code</flux:text>
                <flux:text class="font-medium">{{ $payment->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $payment->date?->format('d M Y') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                @if($payment->isActive())
                    <flux:badge color="green">ACTIVE</flux:badge>
                @else
                    <flux:badge color="red">CANCELLED</flux:badge>
                @endif
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $payment->partner?->name }} ({{ $payment->partner?->code }})</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Company</flux:text>
                <flux:text class="font-medium">{{ $payment->company?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                <flux:text class="font-medium">{{ $payment->currency?->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Payment Method</flux:text>
                <flux:text class="font-medium">{{ $payment->paymentMethod?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Amount</flux:text>
                <flux:text class="font-bold tabular-nums text-lg">{{ number_format((float) $payment->amount, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Reference</flux:text>
                <flux:text class="font-medium">{{ $payment->reference ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Created By</flux:text>
                <flux:text class="font-medium">{{ $payment->createdBy?->name ?? '-' }}</flux:text>
            </div>
            @if($payment->remarks)
                <div class="md:col-span-3">
                    <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                    <flux:text class="font-medium whitespace-pre-line">{{ $payment->remarks }}</flux:text>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Applied Invoices --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Applied Invoices</flux:heading>

        @if($payment->details->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Invoice Code</flux:table.column>
                    <flux:table.column>SO Code</flux:table.column>
                    <flux:table.column>DO Code</flux:table.column>
                    <flux:table.column class="text-center">Invoice Total</flux:table.column>
                    <flux:table.column class="text-center">Payment Amount</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($payment->details as $index => $detail)
                        <flux:table.row :key="'detail-'.$detail->id">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>
                                <a href="{{ route('sales.invoice.show', $detail->invoice->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" wire:navigate>{{ $detail->invoice->code }}</a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $detail->invoice->orderHeader?->code_order ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $detail->invoice->deliveryHeader?->code ?? '-' }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float) $detail->invoice->total, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format((float) $detail->amount, 2) }}</flux:table.cell>
                            <flux:table.cell>
                                @can('view ar invoice')
                                    <flux:button variant="subtle" size="xs" :href="route('sales.invoice.show', $detail->invoice->id)" wire:navigate icon="eye">
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
                <flux:text>No invoice details.</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Cancel Payment Confirmation Modal --}}
    <flux:modal name="cancel-payment-confirmation" class="max-w-sm">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Cancel Payment</flux:heading>
                <flux:text class="mt-2">This will reverse the payment and restore the invoice balance. Please provide a reason.</flux:text>
            </div>
            <flux:textarea wire:model="cancel_reason" label="Cancellation Reason" placeholder="Enter reason for cancellation..." rows="3" required />
            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('cancel-payment-confirmation').close()">Back</flux:button>
                <flux:button variant="danger" icon="no-symbol" wire:click="cancelPayment" x-on:click="$flux.modal('cancel-payment-confirmation').close()">Cancel Payment</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
