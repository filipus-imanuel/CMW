<div>
    <div class="mb-6">
        <flux:button :href="route('sales.order.approval.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Approvals
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Approve Sales Order</flux:heading>
    <flux:subheading class="mb-6">{{ $order->code_request }}</flux:subheading>

    {{-- Customer Check Info Cards --}}
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
                <flux:text class="font-medium">{{ $order->code_request }}</flux:text>
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
                <flux:text>No items in this request.</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Delivery Schedule (read-only) --}}
    <x-sales.delivery-schedule-readonly :order="$order" />

    {{-- Approval Actions --}}
    @canany(['approve sales order', 'reject sales order'])
    <flux:card>
        <flux:heading size="lg" class="mb-4">Approval Decision</flux:heading>

        <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
            Review the request details above, then choose an action.
        </flux:text>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:button :href="route('sales.order.approval.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button wire:click="confirmRestore" variant="filled" icon="arrow-uturn-left">Restore to Draft</flux:button>
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
            <flux:button :href="route('sales.order.approval.index')" variant="primary" wire:navigate>Back to List</flux:button>
        </div>
    </flux:card>
    @endcanany

    {{-- Restore Confirmation Modal --}}
    @can('approve sales order')
    <flux:modal name="restore-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Restore to Draft</flux:heading>
                <flux:subheading class="mt-2">
                    This will return <strong>{{ $order->code_request }}</strong> to <strong>INIT (Draft)</strong> status. The requester will be able to edit and resubmit.
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('restore-confirmation').close()">Cancel</flux:button>
                <flux:button variant="filled" wire:click="processRestore" icon="arrow-uturn-left">Restore</flux:button>
            </div>
        </div>
    </flux:modal>
    @endcan

    {{-- Approve Confirmation Modal --}}
    @can('approve sales order')
    <flux:modal name="approve-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Approve Sales Order</flux:heading>
                <flux:subheading class="mt-2">
                    You are about to approve <strong>{{ $order->code_request }}</strong> for <strong>{{ $order->currency?->code }} {{ number_format((float)$order->total, 2) }}</strong>. A Sales Order code will be generated.
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
    @endcan

    {{-- Reject Confirmation Modal --}}
    @can('reject sales order')
    <flux:modal name="reject-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Reject Sales Order</flux:heading>
                <flux:subheading class="mt-2">
                    Rejecting this request will set it to <strong>REJECTED</strong> status.
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
