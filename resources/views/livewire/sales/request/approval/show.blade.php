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
                    <div class="space-y-1">
                        <div>Total projected exposure: <strong>{{ number_format($checks['debt']['projected'], 2) }}</strong> exceeds credit limit: <strong>{{ number_format($checks['debt']['limit'], 2) }}</strong></div>
                        <div class="text-sm opacity-75">
                            AR Outstanding: {{ number_format($checks['debt']['outstanding'], 2) }}
                            · Pending Orders: {{ number_format($checks['debt']['pending_orders'], 2) }}
                            · This Order: {{ number_format($checks['debt']['current_order'], 2) }}
                        </div>
                    </div>
                </flux:callout.text>
            </flux:callout>
        @elseif(!empty($checks['debt']) && $checks['debt']['limit'] > 0)
            <flux:callout color="blue" icon="information-circle" class="mb-4">
                <flux:callout.heading>Credit Info</flux:callout.heading>
                <flux:callout.text>
                    Credit remaining: <strong>{{ number_format($checks['debt']['remaining'], 2) }}</strong> / {{ number_format($checks['debt']['limit'], 2) }}
                    <span class="text-sm opacity-75">
                        (AR: {{ number_format($checks['debt']['outstanding'], 2) }}
                        · Pending: {{ number_format($checks['debt']['pending_orders'], 2) }}
                        · This Order: {{ number_format($checks['debt']['current_order'], 2) }})
                    </span>
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
                                <td class="py-2 px-2">{{ $detail->itemUom?->uom?->name }}</td>
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
    @can('approve sales request')
    <flux:card>
        <flux:heading size="lg" class="mb-4">Approval Decision</flux:heading>

        <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
            Review the request details above, then approve or reject this sales request.
        </flux:text>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:button :href="route('sales.request.approval.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button wire:click="confirmReject" variant="danger" icon="x-circle">Reject</flux:button>
            <flux:button wire:click="confirmApprove" variant="primary" icon="check-circle">Approve</flux:button>
        </div>
    </flux:card>
    @else
    <flux:card>
        <flux:heading size="lg" class="mb-4">Awaiting Approval</flux:heading>
        
        <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
            This sales request is pending approval from authorized personnel.
        </flux:text>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:button :href="route('sales.request.approval.index')" variant="primary" wire:navigate>Back to List</flux:button>
        </div>
    </flux:card>
    @endcan

    {{-- Approve Confirmation Modal --}}
    @can('approve sales request')
    <flux:modal name="approve-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Approve Sales Request</flux:heading>
                <flux:subheading class="mt-2">
                    You are about to approve request <strong>{{ $order->code }}</strong> for <strong>{{ $order->currency?->code }} {{ number_format((float)$order->total, 2) }}</strong>.
                </flux:subheading>
            </div>

            <flux:textarea
                wire:model="approvalNotes"
                label="Approval Notes (Optional)"
                placeholder="Add any notes about this approval..."
                rows="3"
            />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('approve-confirmation').close()">Cancel</flux:button>
                <flux:button variant="primary" wire:click="processApproval" icon="check-circle">Approve</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Reject Confirmation Modal --}}
    <flux:modal name="reject-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Reject Sales Request</flux:heading>
                <flux:subheading class="mt-2">
                    Rejecting this request will return it to <strong>INIT (Draft)</strong> status. The requester will be able to edit and resubmit.
                </flux:subheading>
            </div>

            <flux:textarea
                wire:model="rejection_reason"
                label="Rejection Reason"
                badge="Required"
                placeholder="Please provide a reason for rejection..."
                rows="3"
                :error="$errors->first('rejection_reason')"
            />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('reject-confirmation').close()">Cancel</flux:button>
                <flux:button variant="danger" wire:click="processReject" icon="x-circle">Reject</flux:button>
            </div>
        </div>
    </flux:modal>
    @endcan
</div>
