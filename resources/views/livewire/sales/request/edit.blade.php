<div>
    <div class="mb-6">
        <flux:button :href="route('sales.request.index.init')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Draft
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Edit Sales Request</flux:heading>
    <flux:subheading class="mb-6">{{ $order->code }}</flux:subheading>

    {{-- Warning Banners --}}
    @if(!empty($checks))
        @if(!empty($checks['debt']) && $checks['debt']['exceeded'])
            <flux:callout color="red" icon="exclamation-triangle" class="mb-4">
                <flux:callout.heading>Credit Limit Exceeded</flux:callout.heading>
                <flux:callout.text>
                    Outstanding balance: {{ number_format($checks['debt']['outstanding'], 2) }} exceeds credit limit: {{ number_format($checks['debt']['limit'], 2) }}.
                    This request will require approval.
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
                <flux:text class="font-medium">{{ $order->code }}</flux:text>
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

            <flux:textarea
                wire:model="inputs.remarks"
                label="Remarks"
                placeholder="Optional notes..."
                rows="2"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
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
    </flux:card>

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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">#</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Code</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Item Name</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">UOM</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Quantity</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Price</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Discount</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Tax</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Total</th>
                            <th class="text-center py-3 px-2 font-medium text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            <tr wire:key="item-{{ $index }}" class="border-b border-zinc-100 dark:border-zinc-800">
                                <td class="py-2 px-2 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="py-2 px-2">{{ $item['item_code'] }}</td>
                                <td class="py-2 px-2">{{ $item['item_name'] }}</td>
                                <td class="py-2 px-2">{{ $item['uom_name'] }}</td>
                                <td class="py-2 px-2">
                                    <flux:input
                                        wire:model.live.debounce.500ms="items.{{ $index }}.quantity"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="text-right"
                                        size="sm"
                                    />
                                </td>
                                <td class="py-2 px-2">
                                    <flux:input
                                        wire:model.live.debounce.500ms="items.{{ $index }}.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="text-right"
                                        size="sm"
                                    />
                                </td>
                                <td class="py-2 px-2">
                                    <flux:input
                                        wire:model.live.debounce.500ms="items.{{ $index }}.discount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="text-right"
                                        size="sm"
                                    />
                                </td>
                                <td class="py-2 px-2 text-right">
                                    {{ number_format((float)$item['tax'], 2) }}
                                </td>
                                <td class="py-2 px-2 text-right font-medium">
                                    {{ number_format((float)$item['total'], 2) }}
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <flux:button
                                        wire:click="confirmDeleteItem({{ $index }})"
                                        variant="danger"
                                        size="xs"
                                        icon="trash"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $sumSubtotal = collect($items)->sum(fn($i) => ((float)$i['quantity'] * (float)$i['price']) - (float)$i['discount']);
                            $sumTax = collect($items)->sum(fn($i) => (float)$i['tax']);
                            $sumTotal = collect($items)->sum(fn($i) => (float)$i['total']);
                        @endphp
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="8" class="py-2 px-2 text-right text-zinc-500">Subtotal:</td>
                            <td class="py-2 px-2 text-right">{{ number_format($sumSubtotal, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="8" class="py-2 px-2 text-right text-zinc-500">
                                Tax
                                @if(($inputs['tax_mode'] ?? 'NONE') !== 'NONE')
                                    ({{ $inputs['tax_mode'] }} {{ number_format((float)($order->tax_rate ?? 0), 2) }}%)
                                @endif
                                :
                            </td>
                            <td class="py-2 px-2 text-right">{{ number_format($sumTax, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="border-t-2 border-zinc-300 dark:border-zinc-600">
                            <td colspan="8" class="py-3 px-2 text-right font-semibold">Grand Total:</td>
                            <td class="py-3 px-2 text-right font-semibold">{{ number_format($sumTotal, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
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
