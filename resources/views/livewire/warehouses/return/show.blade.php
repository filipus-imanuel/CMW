<div>
    <div class="mb-6">
        <flux:button :href="route('warehouses.return.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Return List
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Receive Return Goods</flux:heading>
    <flux:subheading class="mb-6">{{ $returnHeader->code }}</flux:subheading>

    {{-- Header Info --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Return Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Return Code</flux:text>
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
                <flux:text class="text-sm text-zinc-500">Return Type</flux:text>
                <flux:badge :color="\App\Models\CMW\Transaction\ReturnHeader::returnTypeBadgeColor($returnHeader->return_type)" size="sm">{{ $returnHeader->return_type }}</flux:badge>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
            <div>
                <flux:text class="text-sm text-zinc-500">Sales Order</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->orderHeader?->code_order }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Delivery Order</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->deliveryHeader?->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Total</flux:text>
                <flux:text class="font-medium">{{ number_format((float)$returnHeader->total, 2) }}</flux:text>
            </div>
        </div>
    </flux:card>

    {{-- Receipt Items --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Receive Items</flux:heading>
        <flux:subheading class="mb-4">Input the received good and damaged quantities for each item. Good + Damaged must equal Return Qty.</flux:subheading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Item Name</flux:table.column>
                <flux:table.column>UOM</flux:table.column>
                <flux:table.column class="text-center">Return Qty</flux:table.column>
                <flux:table.column class="text-center">Good Qty</flux:table.column>
                <flux:table.column class="text-center">Damaged Qty</flux:table.column>
                <flux:table.column>Warehouse</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($lines as $index => $line)
                    <flux:table.row :key="'line-'.$index">
                        <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                        <flux:table.cell>{{ $line['item_code'] }}</flux:table.cell>
                        <flux:table.cell>{{ $line['item_name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $line['uom_name'] }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums font-semibold">{{ number_format((float)$line['quantity_return'], 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:input wire:model.live.debounce.500ms="lines.{{ $index }}.quantity_received_good"
                                type="number" step="0.01" min="0" max="{{ $line['quantity_return'] }}"
                                class="w-24 text-right" size="sm" />
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:input wire:model.live.debounce.500ms="lines.{{ $index }}.quantity_received_damaged"
                                type="number" step="0.01" min="0" max="{{ $line['quantity_return'] }}"
                                class="w-24 text-right" size="sm" />
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:select wire:model="lines.{{ $index }}.warehouse_id" size="sm">
                                <option value="">-- Select --</option>
                                @foreach($dropdown_warehouses as $wh)
                                    <option value="{{ $wh['value'] }}">{{ $wh['label'] }}</option>
                                @endforeach
                            </flux:select>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Actions --}}
    <div class="flex gap-2 justify-end">
        <flux:button :href="route('warehouses.return.index')" variant="ghost" wire:navigate>Cancel</flux:button>
        @can('receive warehouse return')
            <flux:button variant="primary" icon="check"
                x-on:click="$flux.modal('confirm-receipt').show()">
                Confirm Receipt
            </flux:button>
        @endcan
    </div>

    {{-- Confirm Receipt Modal --}}
    <flux:modal name="confirm-receipt" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Confirm Receipt</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Confirm receipt of returned goods? This will update inventory.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('confirm-receipt').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="confirmReceipt" x-on:click="$flux.modal('confirm-receipt').close()">Yes, Confirm</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
