<div>
    <div class="mb-6">
        @if($returnHeader->isInit())
            <flux:button :href="route('sales.return.index.draft')" variant="ghost" icon="arrow-left" wire:navigate>Back to Draft</flux:button>
        @elseif($returnHeader->isApproval())
            <flux:button :href="route('sales.return.index.approval')" variant="ghost" icon="arrow-left" wire:navigate>Back to Approval</flux:button>
        @elseif($returnHeader->isProcessing())
            <flux:button :href="route('sales.return.index.ongoing')" variant="ghost" icon="arrow-left" wire:navigate>Back to Ongoing</flux:button>
        @elseif($returnHeader->isFinish())
            <flux:button :href="route('sales.return.index.finish')" variant="ghost" icon="arrow-left" wire:navigate>Back to Finished</flux:button>
        @elseif($returnHeader->isRejected())
            <flux:button :href="route('sales.return.index.rejected')" variant="ghost" icon="arrow-left" wire:navigate>Back to Rejected</flux:button>
        @elseif($returnHeader->isCancelled())
            <flux:button :href="route('sales.return.index.cancelled')" variant="ghost" icon="arrow-left" wire:navigate>Back to Cancelled</flux:button>
        @endif
    </div>

    <div class="flex items-center gap-3 mb-6">
        <flux:heading size="xl">{{ $returnHeader->code }}</flux:heading>
        @php
            $statusColor = match($returnHeader->status) {
                'INIT' => 'zinc',
                'APPROVAL' => 'amber',
                'PROCESSING' => 'blue',
                'FINISH' => 'green',
                'CANCELLED' => 'zinc',
                'REJECTED' => 'red',
                default => 'zinc',
            };
        @endphp
        <flux:badge :color="$statusColor" size="lg">{{ $returnHeader->status }}</flux:badge>
        <flux:badge :color="$returnHeader->return_type === 'ITEM' ? 'blue' : 'purple'" size="lg">{{ $returnHeader->return_type }}</flux:badge>
    </div>

    {{-- Status Messages --}}
    @if($returnHeader->isProcessing() && !$returnHeader->isWarehouseReceived())
        <flux:callout color="blue" icon="information-circle" class="mb-6">
            <flux:callout.heading>Pending Warehouse Receipt</flux:callout.heading>
            <flux:callout.text>This return has been approved and is awaiting warehouse to receive the returned goods.</flux:callout.text>
        </flux:callout>
    @endif

    @if($returnHeader->isRejected() && $returnHeader->rejection_reason)
        <flux:callout color="red" icon="x-circle" class="mb-6">
            <flux:callout.heading>Rejection Reason</flux:callout.heading>
            <flux:callout.text>{{ $returnHeader->rejection_reason }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Header Info --}}
    <flux:card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Return Information</flux:heading>
            @if($returnHeader->isProcessing() && !$returnHeader->isWarehouseReceived())
                @can('edit sales return')
                    <flux:button variant="danger" size="sm" icon="x-circle"
                        x-on:click="$flux.modal('cancel-return-confirmation').show()">
                        Cancel Return
                    </flux:button>
                @endcan
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Code</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->date?->format('d M Y') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->partner?->name }} ({{ $returnHeader->partner?->code }})</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Company</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->orderHeader?->company?->name }}</flux:text>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
            <div>
                <flux:text class="text-sm text-zinc-500">Sales Order</flux:text>
                <flux:button variant="subtle" size="xs" :href="route('sales.order.show', ['id' => $returnHeader->order_header_id])" wire:navigate>
                    {{ $returnHeader->orderHeader?->code_order }}
                </flux:button>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Delivery Order</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->deliveryHeader?->code }}</flux:text>
            </div>
            @if($returnHeader->arInvoice)
                <div>
                    <flux:text class="text-sm text-zinc-500">Invoice</flux:text>
                    <flux:text class="font-medium">{{ $returnHeader->arInvoice?->code }}</flux:text>
                </div>
            @endif
            <div>
                <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->orderHeader?->currency?->code }}</flux:text>
            </div>
        </div>

        @if($returnHeader->remarks)
            <div class="mt-4">
                <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                <flux:text>{{ $returnHeader->remarks }}</flux:text>
            </div>
        @endif

        @if($returnHeader->approved_by)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div>
                    <flux:text class="text-sm text-zinc-500">Approved By</flux:text>
                    <flux:text class="font-medium">{{ $returnHeader->approvedByUser?->name }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Approved At</flux:text>
                    <flux:text class="font-medium">{{ $returnHeader->approved_at?->format('d M Y H:i') }}</flux:text>
                </div>
                @if($returnHeader->received_by)
                    <div>
                        <flux:text class="text-sm text-zinc-500">Received By</flux:text>
                        <flux:text class="font-medium">{{ $returnHeader->receivedByUser?->name }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-sm text-zinc-500">Received At</flux:text>
                        <flux:text class="font-medium">{{ $returnHeader->received_at?->format('d M Y H:i') }}</flux:text>
                    </div>
                @endif
            </div>
        @endif
    </flux:card>

    {{-- Items Table --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Return Items</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Item Name</flux:table.column>
                <flux:table.column>UOM</flux:table.column>
                <flux:table.column class="text-center">Return Qty</flux:table.column>
                <flux:table.column class="text-center">Price</flux:table.column>
                <flux:table.column class="text-center">Tax</flux:table.column>
                <flux:table.column class="text-center">Total</flux:table.column>
                @if($returnHeader->isWarehouseReceived())
                    <flux:table.column class="text-center">Good Qty</flux:table.column>
                    <flux:table.column class="text-center">Damaged Qty</flux:table.column>
                @endif
                @if($returnHeader->isFinish() && $returnHeader->return_type === 'ITEM')
                    <flux:table.column class="text-center">Redelivery</flux:table.column>
                    <flux:table.column class="text-center">Next SO</flux:table.column>
                @endif
            </flux:table.columns>
            <flux:table.rows>
                @foreach($returnHeader->details as $index => $detail)
                    <flux:table.row>
                        <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                        <flux:table.cell>{{ $detail->item?->code }}</flux:table.cell>
                        <flux:table.cell>{{ $detail->item?->name }}</flux:table.cell>
                        <flux:table.cell>{{ $detail->itemUom?->uom?->name }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_return, 2) }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->price, 2) }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->tax, 2) }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums font-semibold">{{ number_format((float)$detail->total, 2) }}</flux:table.cell>
                        @if($returnHeader->isWarehouseReceived())
                            <flux:table.cell class="text-right tabular-nums text-green-600">{{ number_format((float)$detail->quantity_received_good, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums text-red-600">{{ number_format((float)$detail->quantity_received_damaged, 2) }}</flux:table.cell>
                        @endif
                        @if($returnHeader->isFinish() && $returnHeader->return_type === 'ITEM')
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_redelivery, 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$detail->quantity_next_so, 2) }}</flux:table.cell>
                        @endif
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div class="mt-3 border-t border-zinc-200 dark:border-zinc-700 pt-3 flex justify-end text-sm">
            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-right font-semibold">
                <span>Subtotal:</span>
                <span class="tabular-nums">{{ number_format((float)$returnHeader->subtotal, 2) }}</span>
                <span>Tax:</span>
                <span class="tabular-nums">{{ number_format((float)$returnHeader->tax, 2) }}</span>
                <span class="text-lg">Grand Total:</span>
                <span class="tabular-nums text-lg">{{ number_format((float)$returnHeader->total, 2) }}</span>
            </div>
        </div>
    </flux:card>

    {{-- APPROVAL Actions --}}
    @if($returnHeader->isApproval())
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Approval Decision</flux:heading>

            <div class="flex gap-4 items-start">
                @can('approve sales return')
                    <flux:button variant="primary" icon="check-circle"
                        x-on:click="$flux.modal('approve-confirmation').show()">
                        Approve
                    </flux:button>
                @endcan

                @can('reject sales return')
                    <flux:button x-on:click="$flux.modal('reject-return-modal').show()" variant="danger" icon="x-circle">
                        Reject
                    </flux:button>
                @endcan
            </div>
        </flux:card>
    @endif

    {{-- Allocation Form (PROCESSING + post-warehouse + ITEM type) --}}
    @if($returnHeader->isProcessing() && $returnHeader->isWarehouseReceived() && $returnHeader->return_type === 'ITEM')
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Item Allocation</flux:heading>
            <flux:subheading class="mb-4">Decide how returned items should be handled.</flux:subheading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Item</flux:table.column>
                    <flux:table.column class="text-center">Return Qty</flux:table.column>
                    <flux:table.column class="text-center">Good Qty</flux:table.column>
                    <flux:table.column class="text-center">Damaged Qty</flux:table.column>
                    <flux:table.column class="text-center">Re-delivery Qty</flux:table.column>
                    <flux:table.column class="text-center">Next SO Qty</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($allocations as $index => $alloc)
                        <flux:table.row :key="'alloc-'.$index">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>{{ $alloc['item_code'] }} - {{ $alloc['item_name'] }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$alloc['quantity_return'], 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums text-green-600">{{ number_format((float)$alloc['quantity_received_good'], 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums text-red-600">{{ number_format((float)$alloc['quantity_received_damaged'], 2) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model.live.debounce.500ms="allocations.{{ $index }}.quantity_redelivery"
                                    type="number" step="0.01" min="0" max="{{ $alloc['quantity_return'] }}"
                                    class="w-24 text-right" size="sm" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model.live.debounce.500ms="allocations.{{ $index }}.quantity_next_so"
                                    type="number" step="0.01" min="0" max="{{ $alloc['quantity_return'] }}"
                                    class="w-24 text-right" size="sm" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-6 flex justify-end">
                @can('edit sales return')
                    <flux:button variant="primary" icon="check"
                        x-on:click="$flux.modal('allocation-confirmation').show()">
                        Confirm Allocation
                    </flux:button>
                @endcan
            </div>
        </flux:card>
    @endif

    {{-- Approve Confirmation Modal --}}
    <flux:modal name="approve-confirmation" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Approve Sales Return</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Approve this return? It will proceed to warehouse for receipt.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('approve-confirmation').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="processApproval" x-on:click="$flux.modal('approve-confirmation').close()">Yes, Approve</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Reject Modal --}}
    <flux:modal name="reject-return-modal">
        <flux:heading>Reject Sales Return</flux:heading>
        <flux:subheading>Please provide a reason for rejecting this return.</flux:subheading>

        <div class="mt-4">
            <flux:textarea wire:model="rejection_reason" label="Rejection Reason" rows="3" placeholder="Enter reason..." />
            @error('rejection_reason') <flux:text class="text-red-500 text-sm mt-1">{{ $message }}</flux:text> @enderror
        </div>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button wire:click="processReject" variant="danger">Reject</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('reject-return-modal').close()">Cancel</flux:button>
        </div>
    </flux:modal>

    {{-- Allocation Confirmation Modal --}}
    <flux:modal name="allocation-confirmation" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Confirm Allocation</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Re-delivery items will reopen the SO for new delivery. Next SO items will be available for future sales requests.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('allocation-confirmation').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="processAllocation" x-on:click="$flux.modal('allocation-confirmation').close()">Yes, Confirm</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Cancel Return Confirmation Modal --}}
    <flux:modal name="cancel-return-confirmation" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Cancel Sales Return</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Cancel this return? This action cannot be undone.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('cancel-return-confirmation').close()">Back</flux:button>
                <flux:button variant="danger" wire:click="cancelReturn" x-on:click="$flux.modal('cancel-return-confirmation').close()">Yes, Cancel</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
