<div>
    <div class="mb-6">
        <flux:button :href="route('sales.invoice.show', ['id' => $invoice->id])" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Invoice
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Record Payment</flux:heading>
    <flux:subheading class="mb-6">For Invoice: {{ $invoice->code }}</flux:subheading>

    {{-- Invoice Summary --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Invoice Summary</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $invoice->partner?->name }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Total</flux:text>
                <flux:text class="font-semibold tabular-nums">{{ number_format((float) $invoice->total, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Paid</flux:text>
                <flux:text class="font-semibold tabular-nums text-green-600 dark:text-green-400">{{ number_format((float) $invoice->paid, 2) }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Outstanding Balance</flux:text>
                <flux:text class="font-bold tabular-nums text-red-600 dark:text-red-400">{{ number_format((float) $invoice->balance, 2) }}</flux:text>
            </div>
        </div>
    </flux:card>

    {{-- Payment Form --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">Payment Details</flux:heading>

        <form wire:submit="store">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input
                    wire:model="inputs.date"
                    label="Payment Date"
                    type="date"
                    required
                />

                <flux:input
                    wire:model="inputs.amount"
                    label="Amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    :max="$invoice->balance"
                    required
                    description="Max: {{ number_format((float) $invoice->balance, 2) }}"
                />

                <flux:select
                    wire:model="inputs.payment_method_id"
                    label="Payment Method"
                    placeholder="Select payment method..."
                    required
                >
                    @foreach($paymentMethods as $method)
                        <flux:select.option :value="$method->id">{{ $method->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="inputs.reference"
                    label="Reference"
                    placeholder="Transfer reference, cheque no, etc."
                />
            </div>

            <div class="mt-6">
                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    placeholder="Optional remarks..."
                    rows="3"
                />
            </div>

            <div class="flex gap-2 mt-6">
                <flux:spacer />
                <flux:button variant="ghost" :href="route('sales.invoice.show', ['id' => $invoice->id])" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary" icon="banknotes">Record Payment</flux:button>
            </div>
        </form>
    </flux:card>
</div>
