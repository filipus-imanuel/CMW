<div>
    <div class="mb-6">
        <flux:button :href="route('sales.return.index.draft')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Draft
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Create Sales Return</flux:heading>
    <flux:subheading class="mb-6">Create a new return from a delivered sales order</flux:subheading>

    {{-- Step 1: Select Customer / SO / DO (only if not pre-loaded) --}}
    @if(!$selectedDelivery)
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Select Delivery Order</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:select wire:model.live="inputs.partner_id" label="Customer" placeholder="Select customer...">
                        <option value="">-- Select Customer --</option>
                        @foreach($dropdown_data['customers'] ?? [] as $customer)
                            <option value="{{ $customer['value'] }}">{{ $customer['label'] }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model.live="inputs.order_id" label="Sales Order" placeholder="Select SO..." :disabled="empty($dropdown_data['orders'])">
                        <option value="">-- Select SO --</option>
                        @foreach($dropdown_data['orders'] ?? [] as $order)
                            <option value="{{ $order['value'] }}">{{ $order['label'] }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model.live="inputs.delivery_id" label="Delivery Order" placeholder="Select DO..." :disabled="empty($dropdown_data['deliveries'])">
                        <option value="">-- Select DO --</option>
                        @foreach($dropdown_data['deliveries'] ?? [] as $delivery)
                            <option value="{{ $delivery['value'] }}">{{ $delivery['label'] }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </flux:card>
    @endif

    @if($selectedDelivery && $selectedOrder)
        {{-- Header Info --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Return Information</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                    <flux:text class="font-medium">{{ $selectedOrder->partner?->name }} ({{ $selectedOrder->partner?->code }})</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Sales Order</flux:text>
                    <flux:text class="font-medium">{{ $selectedOrder->code_order }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Delivery Order</flux:text>
                    <flux:text class="font-medium">{{ $selectedDelivery->code }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Company</flux:text>
                    <flux:text class="font-medium">{{ $selectedOrder->company?->name }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-zinc-500">Currency</flux:text>
                    <flux:text class="font-medium">{{ $selectedOrder->currency?->code }}</flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <flux:select wire:model.live="inputs.return_type" label="Return Type">
                        <option value="ITEM">Item (Re-delivery / Next SO)</option>
                        <option value="INVOICE_RETURN">Invoice (Goods Returned)</option>
                        <option value="INVOICE_DISCARD">Invoice (Goods Discarded)</option>
                    </flux:select>
                </div>
                <div>
                    <flux:input wire:model="inputs.date" type="date" label="Return Date" />
                </div>
                <div>
                    <flux:textarea wire:model="inputs.remarks" label="Remarks" rows="2" />
                </div>
            </div>
            @error('inputs.return_type') <flux:text class="text-red-500 text-sm mt-1">{{ $message }}</flux:text> @enderror
        </flux:card>

        {{-- Items --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Return Items</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-8">Include</flux:table.column>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Code</flux:table.column>
                    <flux:table.column>Item Name</flux:table.column>
                    <flux:table.column>UOM</flux:table.column>
                    <flux:table.column class="text-center">DO Qty</flux:table.column>
                    <flux:table.column class="text-center">Return Qty</flux:table.column>
                    <flux:table.column class="text-center">Price</flux:table.column>
                    <flux:table.column class="text-center">Tax</flux:table.column>
                    <flux:table.column class="text-center">Total</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($items as $index => $item)
                        <flux:table.row :key="'item-'.$index">
                            <flux:table.cell>
                                <flux:checkbox wire:model.live="items.{{ $index }}.include" />
                            </flux:table.cell>
                            <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                            <flux:table.cell>{{ $item['item_code'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['item_name'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['uom_name'] }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['max_quantity'], 2) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:input wire:model.live.debounce.500ms="items.{{ $index }}.quantity_return"
                                    type="number" step="0.01" min="0" max="{{ $item['max_quantity'] }}"
                                    class="w-24 text-right" size="sm" :disabled="!$item['include']" />
                            </flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['price'], 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['tax'], 2) }}</flux:table.cell>
                            <flux:table.cell class="text-right tabular-nums" variant="strong">{{ number_format((float)$item['total'], 2) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-3 border-t border-zinc-200 dark:border-zinc-700 pt-3 flex justify-end text-sm">
                <div class="flex gap-8 font-semibold">
                    <span>Grand Total:</span>
                    <span class="tabular-nums min-w-[120px] text-right">
                        {{ number_format(collect($items)->where('include', true)->sum('total'), 2) }}
                    </span>
                </div>
            </div>
        </flux:card>

        {{-- Actions --}}
        <div class="flex gap-2 justify-end">
            <flux:button :href="route('sales.return.index.draft')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button wire:click="store" variant="primary" icon="check">Create Return</flux:button>
        </div>
    @endif
</div>
