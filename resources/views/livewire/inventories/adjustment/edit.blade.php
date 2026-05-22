<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('inventories.stock-adjustments.show', ['id' => $header->id])" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Detail
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Edit Stock Adjustment</flux:heading>
    <flux:subheading class="mb-6">{{ $header->code }}</flux:subheading>

    <form wire:submit="update">
        {{-- Header Details --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Adjustment Details</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:date-picker
                        wire:model="inputs.date"
                        label="Date"
                        badge="Required"
                    />

                    <flux:select
                        wire:model.live="inputs.warehouse_id"
                        label="Warehouse"
                        badge="Required"
                        placeholder="Select Warehouse"
                        searchable
                    >
                        @foreach($dropdown_warehouses as $warehouse)
                            <flux:select.option value="{{ $warehouse['value'] }}">{{ $warehouse['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    rows="3"
                    placeholder="Enter remarks (optional)"
                    maxlength="1024"
                />
            </div>
        </flux:card>

        {{-- Work Order Link --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Work Order (optional)</flux:heading>

            <div class="space-y-4">
                <flux:radio.group wire:model.live="soFilter" label="Filter SO" variant="segmented">
                    <flux:radio value="has_wo" label="Has WO" />
                    <flux:radio value="no_wo" label="No WO" />
                    <flux:radio value="all" label="All" />
                </flux:radio.group>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:date-picker
                        wire:model.live="soDateFrom"
                        label="SO Date From"
                    />
                    <flux:date-picker
                        wire:model.live="soDateTo"
                        label="SO Date To"
                    />
                </div>

                <flux:select
                    wire:model.live="inputs.order_header_id"
                    variant="combobox"
                    :filter="false"
                    label="Sales Order"
                    placeholder="Search SO code or WO number..."
                    clearable
                >
                    <x-slot name="input">
                        <flux:select.input
                            wire:model.live.debounce.300ms="soSearch"
                            placeholder="Type SO/WO to search (server-side)..."
                        />
                    </x-slot>

                    @foreach($this->orderOptions as $order)
                        <flux:select.option value="{{ $order['id'] }}" wire:key="so-{{ $order['id'] }}">
                            {{ $order['label'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.work_order_auto"
                        label="WO Auto"
                        placeholder="Auto-generated from SO"
                        readonly
                        disabled
                    />
                    <flux:input
                        wire:model="inputs.work_order_manual"
                        label="WO Manual"
                        placeholder="Manual work order number"
                        maxlength="100"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:date-picker
                        wire:model="inputs.production_date"
                        label="Production Date"
                        clearable
                    />
                </div>
            </div>
        </flux:card>

        {{-- Line Items --}}
        <flux:card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Adjustment Lines</flux:heading>
                <flux:button type="button" icon="plus" size="sm" variant="primary" wire:click="addLine">
                    Add Line
                </flux:button>
            </div>

            @error('lines')
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">{{ $message }}</flux:callout>
            @enderror

            @if(count($lines) > 0)
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
                            <flux:table.column class="text-center w-20">Actions</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($lines as $i => $line)
                                <flux:table.row wire:key="line-{{ $i }}">
                                    <flux:table.cell>{{ $i + 1 }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:select
                                            wire:model.live="lines.{{ $i }}.item_id"
                                            variant="listbox"
                                            placeholder="Select Item"
                                            size="sm"
                                            searchable
                                        >
                                            @foreach($dropdown_items as $item)
                                                <flux:select.option value="{{ $item['value'] }}">{{ $item['label'] }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        @error("lines.{$i}.item_id")
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:select
                                            wire:model.live="lines.{{ $i }}.item_uom_id"
                                            placeholder="Select UOM"
                                            size="sm"
                                            :disabled="empty($line['item_id'])"
                                        >
                                            @foreach($line['uom_options'] ?? [] as $uom)
                                                <flux:select.option value="{{ $uom['value'] }}">{{ $uom['label'] }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        @error("lines.{$i}.item_uom_id")
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right tabular-nums">
                                        <span class="text-sm">
                                            {{ $line['quantity_system'] ?? '0' }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right tabular-nums">
                                        <flux:input
                                            wire:model.live.debounce.500ms="lines.{{ $i }}.quantity_actual"
                                            type="number"
                                            step="0.01"
                                            placeholder="0.00"
                                            size="sm"
                                        />
                                        @error("lines.{$i}.quantity_actual")
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right tabular-nums">
                                        @php $diff = (float) ($line['quantity_difference'] ?? 0); @endphp
                                        <span class="text-sm {{ $diff > 0 ? 'text-green-500' : ($diff < 0 ? 'text-red-500' : 'text-zinc-500') }} font-medium">
                                            {{ $line['quantity_difference'] ?? '0' }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:input
                                            wire:model="lines.{{ $i }}.remarks"
                                            placeholder="Optional"
                                            size="sm"
                                        />
                                    </flux:table.cell>
                                    <flux:table.cell class="text-center">
                                        <flux:button
                                            type="button"
                                            variant="danger"
                                            icon="trash"
                                            size="xs"
                                            wire:click="removeLine({{ $i }})"
                                        />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @else
                <flux:callout variant="info" icon="information-circle">
                    No line items added yet. Click "Add Line" to begin adding items for adjustment.
                </flux:callout>
            @endif
        </flux:card>

        {{-- Footer Actions --}}
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button :href="route('inventories.stock-adjustments.show', ['id' => $header->id])" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">Update</flux:button>
        </div>
    </form>
</div>
