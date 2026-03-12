<div>
    <div class="mb-6">
        <flux:button :href="route('sales.request.edit', $order->id)" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Edit
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Delivery Schedule</flux:heading>
    <flux:subheading class="mb-6">{{ $order->code_request }}</flux:subheading>

    {{-- Order Info (read-only) --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Request Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Code</flux:text>
                <flux:text class="font-medium">{{ $order->code_request }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $order->partner?->name }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Company</flux:text>
                <flux:text class="font-medium">{{ $order->company?->name }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Item Category</flux:text>
                <flux:text class="font-medium">{{ $order->itemCategory?->name }}</flux:text>
            </div>
        </div>
    </flux:card>

    {{-- Per-item schedule cards --}}
    @foreach($detailMeta as $detailIndex => $meta)
        @php
            $balance = $this->getBalance($detailIndex);
            $mode = $inputMode[$detailIndex] ?? 'qty';
        @endphp
        <flux:card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="lg">
                        #{{ $detailIndex + 1 }} — {{ $meta['item_code'] }} · {{ $meta['item_name'] }}
                    </flux:heading>
                    <flux:subheading>
                        UOM: {{ $meta['uom_name'] }} · Total Qty: {{ number_format((float)$meta['quantity'], 2) }}
                    </flux:subheading>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Balance indicator --}}
                    @if($balance['balanced'])
                        <flux:badge color="green" size="sm" icon="check-circle">Balanced</flux:badge>
                    @elseif($balance['remaining'] > 0)
                        <flux:badge color="amber" size="sm" icon="exclamation-triangle">Remaining: {{ number_format($balance['remaining'], 2) }}</flux:badge>
                    @else
                        <flux:badge color="red" size="sm" icon="exclamation-triangle">Excess: {{ number_format(abs($balance['remaining']), 2) }}</flux:badge>
                    @endif

                    {{-- Input mode toggle --}}
                    <flux:button wire:click="toggleInputMode({{ $detailIndex }})" variant="subtle" size="sm">
                        Mode: {{ $mode === 'qty' ? 'Quantity' : 'Percentage' }}
                    </flux:button>
                </div>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column class="text-center">Delivery Date</flux:table.column>
                    <flux:table.column class="text-center">Quantity</flux:table.column>
                    <flux:table.column class="text-center">%</flux:table.column>
                    <flux:table.column>Remarks</flux:table.column>
                    <flux:table.column class="w-16 text-center">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($schedules[$detailIndex] ?? [] as $rowIndex => $row)
                        <flux:table.row wire:key="schedule-{{ $detailIndex }}-{{ $rowIndex }}">
                            <flux:table.cell>{{ $rowIndex + 1 }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:date-picker
                                    wire:model.live="schedules.{{ $detailIndex }}.{{ $rowIndex }}.delivery_date"
                                    size="sm"
                                />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    wire:model.live.debounce.500ms="schedules.{{ $detailIndex }}.{{ $rowIndex }}.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="text-right"
                                    size="sm"
                                    :disabled="$mode === 'pct'"
                                />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    wire:model.live.debounce.500ms="schedules.{{ $detailIndex }}.{{ $rowIndex }}.percentage"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="text-right"
                                    size="sm"
                                    suffix="%"
                                    :disabled="$mode === 'qty'"
                                />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    wire:model="schedules.{{ $detailIndex }}.{{ $rowIndex }}.remarks"
                                    placeholder="Optional..."
                                    size="sm"
                                />
                            </flux:table.cell>
                            <flux:table.cell>
                                @if(count($schedules[$detailIndex]) > 1)
                                    <flux:button
                                        wire:click="removeRow({{ $detailIndex }}, {{ $rowIndex }})"
                                        variant="danger"
                                        size="xs"
                                        icon="trash"
                                    />
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            {{-- Summary row --}}
            <div class="mt-2 flex items-center justify-between">
                <flux:button wire:click="addRow({{ $detailIndex }})" variant="subtle" size="sm" icon="plus">
                    Add Row
                </flux:button>

                <div class="text-sm text-zinc-500">
                    Scheduled: <span class="tabular-nums font-medium {{ $balance['balanced'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($balance['scheduled'], 2) }}</span>
                    / {{ number_format($balance['total'], 2) }}
                </div>
            </div>
        </flux:card>
    @endforeach

    {{-- Save / Cancel --}}
    <div class="flex gap-2 mt-6">
        <flux:spacer/>
        <flux:button :href="route('sales.request.edit', $order->id)" variant="ghost" wire:navigate>Cancel</flux:button>
        <flux:button wire:click="save" variant="primary" icon="check">Save Schedule</flux:button>
    </div>
</div>
