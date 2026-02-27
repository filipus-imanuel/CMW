<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('warehouses.delivery.upcoming.show', ['id' => $order->id])" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Sales Order
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Create Delivery Order</flux:heading>
    <flux:subheading class="mb-6">SO: {{ $order->code_order ?? $order->code_request ?? '-' }}</flux:subheading>

    <form wire:submit="store">
        {{-- Header Details --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Delivery Details</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:date-picker
                    wire:model="inputs.date"
                    label="Delivery Date"
                    badge="Required"
                />

                <div>
                    <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                    <flux:text class="font-medium">{{ $inputs['partner_name'] ?? '-' }}</flux:text>
                </div>

                <div>
                    <flux:text class="text-sm text-zinc-500">Company</flux:text>
                    <flux:text class="font-medium">{{ $inputs['company_name'] ?? '-' }}</flux:text>
                </div>

                <div>
                    <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                    <flux:text class="font-medium">{{ $inputs['currency_code'] ?? '-' }}</flux:text>
                </div>
            </div>

            <div class="mt-4">
                <flux:select
                    wire:model.live="inputs.selected_address_id"
                    variant="listbox"
                    searchable
                    label="Partner Address"
                    placeholder="Select address..."
                >
                    @foreach($dropdown_addresses as $addr)
                        <flux:select.option value="{{ $addr['value'] }}">{{ $addr['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="mt-4">
                <flux:textarea
                    wire:model="inputs.delivery_address"
                    label="Delivery Address"
                    rows="3"
                    placeholder="Enter or edit delivery address"
                    maxlength="1024"
                />
            </div>

            <div class="mt-4">
                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    rows="3"
                    placeholder="Enter remarks (optional)"
                    maxlength="1024"
                />
            </div>
        </flux:card>

        {{-- Delivery Lines --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Delivery Lines</flux:heading>

            @if(count($lines) > 0)
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-8">#</flux:table.column>
                            <flux:table.column>Item</flux:table.column>
                            <flux:table.column>UOM</flux:table.column>
                            <flux:table.column class="text-center">Ordered</flux:table.column>
                            <flux:table.column class="text-center">Delivered</flux:table.column>
                            <flux:table.column class="text-center">Remaining</flux:table.column>
                            <flux:table.column class="text-center">Warehouse</flux:table.column>
                            <flux:table.column class="text-center">Available</flux:table.column>
                            <flux:table.column class="text-center">Qty to Send</flux:table.column>
                            <flux:table.column class="text-center">Price</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($lines as $i => $line)
                                @if($line['enabled'])
                                    <flux:table.row wire:key="line-{{ $i }}">
                                        <flux:table.cell>{{ $loop->iteration }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:text class="font-medium">{{ $line['item_name'] }}</flux:text>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $line['uom_name'] ?? '-' }}</flux:table.cell>
                                        <flux:table.cell class="text-right tabular-nums">
                                            {{ number_format((float)($line['quantity_ordered'] ?? 0), 2) }}
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right tabular-nums">
                                            {{ number_format((float)($line['quantity_delivered'] ?? 0), 2) }}
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right tabular-nums">
                                            {{ number_format((float)($line['quantity_remaining'] ?? 0), 2) }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:select
                                                wire:model.live="lines.{{ $i }}.warehouse_id"
                                                placeholder="Select"
                                                size="sm"
                                            >
                                                @foreach($line['warehouse_options'] ?? [] as $wh)
                                                    <flux:select.option value="{{ $wh['value'] }}">{{ $wh['label'] }}</flux:select.option>
                                                @endforeach
                                            </flux:select>
                                            @error("lines.{$i}.warehouse_id")
                                                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                            @enderror
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right tabular-nums">
                                            @if($line['quantity_available'] !== null)
                                                <span @class([
                                                    'text-red-600 font-semibold' => (float)$line['quantity_available'] <= 0,
                                                    'text-amber-600 font-medium' => (float)$line['quantity_available'] > 0 && (float)$line['quantity_available'] < (float)$line['quantity_remaining'],
                                                    'text-green-600' => (float)$line['quantity_available'] >= (float)$line['quantity_remaining'],
                                                ])>
                                                    {{ number_format((float)$line['quantity_available'], 2) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-400 text-xs">Select warehouse</span>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @php
                                                $maxQty = (float)($line['quantity_remaining'] ?? 0);
                                                if ($line['quantity_available'] !== null) {
                                                    $maxQty = min($maxQty, (float)$line['quantity_available']);
                                                }
                                            @endphp
                                            <flux:input
                                                wire:model.live.debounce.500ms="lines.{{ $i }}.quantity_sent"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :max="$maxQty"
                                                placeholder="0.00"
                                                size="sm"
                                            />
                                            @error("lines.{$i}.quantity_sent")
                                                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                            @enderror
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right tabular-nums">
                                            {{ number_format((float)($line['price'] ?? 0), 2) }}
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endif
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @else
                <flux:callout variant="info" icon="information-circle">
                    No items available for delivery.
                </flux:callout>
            @endif
        </flux:card>

        {{-- Footer Actions --}}
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button :href="route('warehouses.delivery.upcoming.show', ['id' => $order->id])" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary" icon="paper-airplane">Create Delivery Order</flux:button>
        </div>
    </form>
</div>
