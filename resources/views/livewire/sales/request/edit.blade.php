<div>
    <div class="mb-6">
        <flux:button :href="route('sales.request.index.init')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Draft
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Edit Sales Request</flux:heading>
    <flux:subheading class="mb-6">{{ $order->code_request }}</flux:subheading>

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
                    This request will require approval.
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
                    This request will require approval.
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
                    This request will require approval.
                </flux:callout.text>
            </flux:callout>
        @endif

        @if(!empty($order->rejection_reason))
            <flux:callout color="red" icon="x-circle" class="mb-4">
                <flux:callout.heading>Previously Rejected</flux:callout.heading>
                <flux:callout.text>{{ $order->rejection_reason }}</flux:callout.text>
            </flux:callout>
        @endif
    @endif

    {{-- Header Information --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Request Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Code</flux:text>
                <flux:text class="font-medium">{{ $order->code_request }}</flux:text>
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
                <flux:text class="text-sm text-zinc-500">Status</flux:text>
                <flux:badge color="zinc">{{ $order->status }}</flux:badge>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <flux:date-picker
                wire:model="inputs.date"
                label="Date"
                badge="Required"
            />

            <flux:date-picker
                wire:model="inputs.delivery_date"
                label="Delivery Date"
                placeholder="Select delivery date..."
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <flux:textarea
                wire:model="inputs.remarks"
                label="Remarks"
                placeholder="Optional notes..."
                rows="2"
            />

            <div class="flex flex-col gap-4">
                <div>
                    <flux:radio.group wire:model.live="inputs.tax_mode" label="Tax Mode" badge="Required" variant="segmented">
                        <flux:radio value="INCLUDE" label="Include" />
                        <flux:radio value="EXCLUDE" label="Exclude" />
                        <flux:radio value="NONE" label="No Tax" />
                    </flux:radio.group>
                    @error('inputs.tax_mode')
                        <flux:text class="text-sm text-red-500 mt-1">{{ $message }}</flux:text>
                    @enderror
                </div>

                @if(($inputs['tax_mode'] ?? 'NONE') !== 'NONE')
                    <flux:select
                        wire:model.live="inputs.tax_id"
                        label="Tax"
                        badge="Required"
                        placeholder="Select tax..."
                        :error="$errors->first('inputs.tax_id')"
                    >
                        @foreach($dropdown_data['taxes'] ?? [] as $tax)
                            <flux:select.option value="{{ $tax['value'] }}">{{ $tax['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>
        </div>
    </flux:card>

    {{-- Return Items Available --}}
    @if(count($returnItems) > 0)
        <flux:card class="mt-6">
            <flux:heading size="lg" class="mb-2">Return Items Available</flux:heading>
            <flux:subheading class="mb-4">These items are available from finished sales returns for the same customer. Check items to include them in this request (price defaults to 0).</flux:subheading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8"></flux:table.column>
                    <flux:table.column>Return Code</flux:table.column>
                    <flux:table.column>Item Code</flux:table.column>
                    <flux:table.column>Item Name</flux:table.column>
                    <flux:table.column class="text-center">Qty Return</flux:table.column>
                    <flux:table.column>UOM</flux:table.column>
                    <flux:table.column>Original SO</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($returnItems as $ri)
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:checkbox
                                    wire:click="toggleReturnItem({{ $ri['return_detail_id'] }})"
                                    :checked="in_array($ri['return_detail_id'], $selectedReturnItems)"
                                />
                            </flux:table.cell>
                            <flux:table.cell>{{ $ri['return_code'] }}</flux:table.cell>
                            <flux:table.cell>{{ $ri['item_code'] }}</flux:table.cell>
                            <flux:table.cell>{{ $ri['item_name'] }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format($ri['quantity_next_so'], 2) }}</flux:table.cell>
                            <flux:table.cell>{{ $ri['uom_name'] }}</flux:table.cell>
                            <flux:table.cell>{{ $ri['original_so_code'] }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    {{-- Items Section --}}
    <flux:card>
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Items</flux:heading>
            <flux:button
                wire:click="$dispatch('sales.request.search-item.open', { itemCategoryId: {{ $order->item_category_id }}, partnerId: {{ $order->partner_id }} })"
                variant="primary"
                icon="plus"
                size="sm"
            >
                Add Item
            </flux:button>
        </div>

        @if(count($items) > 0)
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
                    <flux:table.column class="text-center">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($items as $index => $item)
                        @php
                            $guardrail = $priceGuardrails[$index] ?? null;
                            $warnings = $guardrail['warnings'] ?? [];
                        @endphp
                        <flux:table.row :key="'item-'.$index">
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>{{ $item['item_code'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['item_name'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['uom_name'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    wire:model.live.debounce.500ms="items.{{ $index }}.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="text-right"
                                    size="sm"
                                />
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    wire:model.live.debounce.500ms="items.{{ $index }}.price_proposed"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="text-right"
                                    size="sm"
                                />
                                @if($guardrail)
                                    <div class="mt-1 text-xs text-zinc-400">
                                        HET: {{ number_format($guardrail['het_price'], 2) }}
                                        · Floor: {{ number_format($guardrail['floor_price'], 2) }}
                                    </div>
                                    @if(in_array('above_het', $warnings))
                                        <div class="mt-0.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                            <flux:icon.exclamation-triangle class="size-3" />
                                            Price exceeds HET
                                        </div>
                                    @endif
                                    @if(in_array('below_floor', $warnings))
                                        <div class="mt-0.5 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                            <flux:icon.exclamation-triangle class="size-3" />
                                            Price below floor
                                        </div>
                                    @endif
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input
                                    wire:model.live.debounce.500ms="items.{{ $index }}.price_deal"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="text-right"
                                    size="sm"
                                />
                            </flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['discount'], 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['tax'], 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format((float)$item['total'], 2) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button
                                    wire:click="confirmDeleteItem({{ $index }})"
                                    variant="danger"
                                    size="xs"
                                    icon="trash"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @php
                $sumSubtotal = collect($items)->sum(fn($i) => ((float)$i['quantity'] * (float)$i['price_proposed']) - (float)$i['discount']);
                $sumTax = collect($items)->sum(fn($i) => (float)$i['tax']);
                $sumTotal = collect($items)->sum(fn($i) => (float)$i['total']);
            @endphp
            <div class="mt-3 border-t border-zinc-200 dark:border-zinc-700 pt-3 flex flex-col items-end gap-1 text-sm">
                <div class="flex gap-8">
                    <span class="text-zinc-500">Subtotal:</span>
                    <span class="tabular-nums min-w-[120px] text-right">{{ number_format($sumSubtotal, 2) }}</span>
                </div>
                <div class="flex gap-8">
                    <span class="text-zinc-500">
                        Tax
                        @if(($inputs['tax_mode'] ?? 'NONE') !== 'NONE')
                            ({{ $inputs['tax_mode'] }} {{ number_format((float)($order->tax_rate ?? 0), 2) }}%)
                        @endif
                        :
                    </span>
                    <span class="tabular-nums min-w-[120px] text-right">{{ number_format($sumTax, 2) }}</span>
                </div>
                <div class="flex gap-8 border-t border-zinc-300 dark:border-zinc-600 pt-2 mt-1 font-semibold">
                    <span>Grand Total:</span>
                    <span class="tabular-nums min-w-[120px] text-right">{{ number_format($sumTotal, 2) }}</span>
                </div>
            </div>
        @else
            <div class="text-center py-12 text-zinc-400">
                <flux:icon.cube-transparent class="mx-auto mb-4 size-12" />
                <flux:text>No items added yet. Click "Add Item" to start.</flux:text>
            </div>
        @endif

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button :href="route('sales.request.index.init')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button wire:click="save" variant="filled">Save Draft</flux:button>
            <flux:button wire:click="submit" variant="primary">Submit Request</flux:button>
        </div>
    </flux:card>

    {{-- Search Item Modal --}}
    <livewire:sales.request.search-item />

    {{-- Delete Item Confirmation Modal --}}
    <flux:modal name="delete-item-confirmation">
        <flux:heading>Remove Item</flux:heading>
        <flux:subheading>Are you sure you want to remove this item from the request?</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroyItem">Remove</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-item-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
