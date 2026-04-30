<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('inventories.stock-adjustments.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Stock Adjustments
        </flux:button>
    </div>

    <div class="flex items-center justify-between mb-2">
        <flux:heading size="xl">Stock Adjustment Detail</flux:heading>

        <div class="flex gap-2">
            @if($header->isDraft())
                @can('edit stock adjustment')
                <flux:button
                    :href="route('inventories.stock-adjustments.edit', ['id' => $header->id])"
                    variant="outline"
                    icon="pencil-square"
                    wire:navigate
                >
                    Edit
                </flux:button>
                @endcan

                @can('confirm stock adjustment')
                <flux:button
                    variant="primary"
                    icon="check-circle"
                    x-on:click="$flux.modal('confirm-adjustment').show()"
                >
                    Confirm
                </flux:button>
                @endcan

                @can('cancel stock adjustment')
                <flux:button
                    variant="danger"
                    icon="x-circle"
                    x-on:click="$flux.modal('cancel-draft-adjustment').show()"
                >
                    Cancel
                </flux:button>
                @endcan
            @elseif($header->isConfirmed())
                @can('cancel stock adjustment')
                <flux:button
                    variant="danger"
                    icon="x-circle"
                    x-on:click="$flux.modal('cancel-confirmed-adjustment').show()"
                >
                    Cancel (Reverse)
                </flux:button>
                @endcan
            @endif
        </div>
    </div>

    {{-- Confirm Adjustment Modal --}}
    <flux:modal name="confirm-adjustment" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Confirm Stock Adjustment</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Are you sure you want to confirm this stock adjustment? This will update the inventory ledger and lock the document.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('confirm-adjustment').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="confirm" x-on:click="$flux.modal('confirm-adjustment').close()">Yes, Confirm</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Cancel Draft Adjustment Modal --}}
    <flux:modal name="cancel-draft-adjustment" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Cancel Stock Adjustment</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Are you sure you want to cancel this stock adjustment?</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('cancel-draft-adjustment').close()">Back</flux:button>
                <flux:button variant="danger" wire:click="cancel" x-on:click="$flux.modal('cancel-draft-adjustment').close()">Yes, Cancel</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Cancel Confirmed Adjustment Modal --}}
    <flux:modal name="cancel-confirmed-adjustment" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Cancel &amp; Reverse Stock Adjustment</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Are you sure you want to cancel this confirmed stock adjustment? Reversal entries will be created in the inventory ledger.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('cancel-confirmed-adjustment').close()">Back</flux:button>
                <flux:button variant="danger" wire:click="cancel" x-on:click="$flux.modal('cancel-confirmed-adjustment').close()">Yes, Cancel &amp; Reverse</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:subheading class="mb-6">{{ $header->code }}</flux:subheading>

    {{-- Header Information --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Adjustment Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Code</flux:text>
                <flux:text class="font-medium">{{ $header->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $header->date?->format('d M Y') }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                @if($header->isDraft())
                    <flux:badge color="yellow">Draft</flux:badge>
                @elseif($header->isConfirmed())
                    <flux:badge color="green">Confirmed</flux:badge>
                @elseif($header->isCancelled())
                    <flux:badge color="red">Cancelled</flux:badge>
                @else
                    <flux:badge>{{ $header->status }}</flux:badge>
                @endif
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Warehouse</flux:text>
                <flux:text class="font-medium">{{ $header->warehouse?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Sales Order</flux:text>
                <flux:text class="font-medium">{{ $header->orderHeader?->code_order ?? $header->orderHeader?->code_request ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">WO Auto</flux:text>
                <flux:text class="font-medium">{{ $header->work_order_auto ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">WO Manual</flux:text>
                <flux:text class="font-medium">{{ $header->work_order_manual ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Created By</flux:text>
                <flux:text class="font-medium">{{ $header->createdBy?->name ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Last Updated By</flux:text>
                <flux:text class="font-medium">{{ $header->updatedBy?->name ?? '-' }}</flux:text>
            </div>
        </div>

        @if($header->remarks)
            <div class="mt-4">
                <flux:text class="text-sm text-zinc-500">Remarks</flux:text>
                <flux:text class="whitespace-pre-line">{{ $header->remarks }}</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Adjustment Lines --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Adjustment Lines</flux:heading>

        @if($header->details->count() > 0)
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8">#</flux:table.column>
                        <flux:table.column>Item</flux:table.column>
                        <flux:table.column>UOM</flux:table.column>
                        <flux:table.column class="text-center">System Qty</flux:table.column>
                        <flux:table.column class="text-center">Actual Qty</flux:table.column>
                        <flux:table.column class="text-center">Difference</flux:table.column>
                        <flux:table.column>Remarks</flux:table.column>
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
                                <flux:table.cell>
                                    {{ $detail->itemUom?->uom?->code ?? '-' }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    {{ number_format($detail->quantity_system, 2) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    {{ number_format($detail->quantity_actual, 2) }}
                                </flux:table.cell>
                                <flux:table.cell class="text-right tabular-nums">
                                    @php
                                        $diff = (float) $detail->quantity_difference;
                                    @endphp
                                    <span class="{{ $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-zinc-500') }} font-medium">
                                        {{ number_format($diff, 2) }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell>{{ $detail->remarks ?? '-' }}</flux:table.cell>
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

    {{-- Audit Trail: Inventory Ledger Entries --}}
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
