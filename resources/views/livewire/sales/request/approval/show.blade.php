<div>
    <div class="mb-6">
        <flux:button :href="route('sales.request.approval.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Approvals
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Approve Sales Request</flux:heading>
    <flux:subheading class="mb-6">{{ $order->code }}</flux:subheading>

    {{-- Warning Banners --}}
    @if(!empty($checks))
        @if(!empty($checks['debt']) && $checks['debt']['exceeded'])
            <flux:callout color="red" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Credit Limit Exceeded</flux:callout.heading>
                <flux:callout.text>
                    Outstanding balance: {{ number_format($checks['debt']['outstanding'], 2) }} exceeds credit limit: {{ number_format($checks['debt']['limit'], 2) }}.
                </flux:callout.text>
            </flux:callout>
        @endif

        @if(!empty($checks['deliveries']) && $checks['deliveries'] > 0)
            <flux:callout color="amber" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Pending Deliveries</flux:callout.heading>
                <flux:callout.text>
                    This customer has {{ $checks['deliveries'] }} pending delivery/order(s).
                </flux:callout.text>
            </flux:callout>
        @endif

        @if(!empty($checks['limit']) && $checks['limit']['exceeded'])
            <flux:callout color="red" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Company Sales Limit Exceeded</flux:callout.heading>
                <flux:callout.text>
                    Current total: {{ number_format($checks['limit']['current'], 2) }} / Limit: {{ number_format($checks['limit']['limit'], 2) }}.
                    @if($checks['limit']['reason'])
                        {{ $checks['limit']['reason'] }}
                    @endif
                </flux:callout.text>
            </flux:callout>
        @endif
    @endif

    {{-- Request Information --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Request Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Code</flux:text>
                <flux:text class="font-medium">{{ $order->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $order->date?->format('d M Y') }}</flux:text>
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
            <div>
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                <flux:badge color="amber">{{ $order->status }}</flux:badge>
            </div>
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-left py-3 px-2 font-medium text-zinc-500">#</th>
                            <th class="text-left py-3 px-2 font-medium text-zinc-500">Code</th>
                            <th class="text-left py-3 px-2 font-medium text-zinc-500">Item Name</th>
                            <th class="text-left py-3 px-2 font-medium text-zinc-500">UOM</th>
                            <th class="text-right py-3 px-2 font-medium text-zinc-500">Quantity</th>
                            <th class="text-right py-3 px-2 font-medium text-zinc-500">Price</th>
                            <th class="text-right py-3 px-2 font-medium text-zinc-500">Discount</th>
                            <th class="text-right py-3 px-2 font-medium text-zinc-500">Tax</th>
                            <th class="text-right py-3 px-2 font-medium text-zinc-500">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->details as $index => $detail)
                            <tr wire:key="detail-{{ $detail->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                                <td class="py-2 px-2 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="py-2 px-2">{{ $detail->item?->code }}</td>
                                <td class="py-2 px-2">{{ $detail->item?->name }}</td>
                                <td class="py-2 px-2">{{ $detail->uom?->name }}</td>
                                <td class="py-2 px-2 text-right">{{ number_format((float)$detail->quantity, 2) }}</td>
                                <td class="py-2 px-2 text-right">{{ number_format((float)$detail->price, 2) }}</td>
                                <td class="py-2 px-2 text-right">{{ number_format((float)$detail->discount, 2) }}</td>
                                <td class="py-2 px-2 text-right">{{ number_format((float)$detail->tax, 2) }}</td>
                                <td class="py-2 px-2 text-right font-medium">{{ number_format((float)$detail->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-zinc-300 dark:border-zinc-600">
                            <td colspan="8" class="py-3 px-2 text-right font-semibold">Grand Total:</td>
                            <td class="py-3 px-2 text-right font-semibold">{{ number_format((float)$order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-zinc-400">
                <flux:text>No items in this request.</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Approval Actions --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">Approval Decision</flux:heading>

        <div class="mb-4">
            <flux:textarea
                wire:model="rejection_reason"
                label="Rejection Reason"
                placeholder="Required only if rejecting..."
                rows="3"
                :error="$errors->first('rejection_reason')"
            />
        </div>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:button :href="route('sales.request.approval.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button wire:click="reject" variant="danger" wire:confirm="Are you sure you want to reject this request?">Reject</flux:button>
            <flux:button wire:click="approve" variant="primary" wire:confirm="Are you sure you want to approve this request?">Approve</flux:button>
        </div>
    </flux:card>
</div>
