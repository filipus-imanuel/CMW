<div>
    <div class="mb-6">
        <flux:button :href="route('sales.return.index.draft')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Draft
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Edit Sales Return</flux:heading>
    <flux:subheading class="mb-6">{{ $returnHeader->code }}</flux:subheading>

    {{-- Header Info (read-only) --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Return Information</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <flux:text class="text-sm text-zinc-500">Customer</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->partner?->name }} ({{ $returnHeader->partner?->code }})</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Sales Order</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->orderHeader?->code_order }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Delivery Order</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->deliveryHeader?->code }}</flux:text>
            </div>
            <div>
                <flux:text class="text-sm text-zinc-500">Return Type</flux:text>
                <flux:badge :color="\App\Models\CMW\Transaction\ReturnHeader::returnTypeBadgeColor($returnHeader->return_type)" size="sm">{{ $returnHeader->return_type }}</flux:badge>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
            <div>
                <flux:text class="text-sm text-zinc-500">Date</flux:text>
                <flux:text class="font-medium">{{ $returnHeader->date?->format('d M Y') }}</flux:text>
            </div>
            <div class="md:col-span-3">
                <flux:textarea wire:model="inputs.remarks" label="Remarks" rows="2" />
            </div>
        </div>
    </flux:card>

    {{-- Items --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Return Items</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Item Name</flux:table.column>
                <flux:table.column>UOM</flux:table.column>
                <flux:table.column class="text-center">DO Qty</flux:table.column>
                <flux:table.column class="text-center">Return Qty</flux:table.column>
                <flux:table.column class="text-center">Price</flux:table.column>
                <flux:table.column class="text-center">Tax</flux:table.column>
                <flux:table.column class="text-center">Total</flux:table.column>
                <flux:table.column class="w-12"></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($items as $index => $item)
                    <flux:table.row :key="'item-'.$index">
                        <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                        <flux:table.cell>{{ $item['item_code'] }}</flux:table.cell>
                        <flux:table.cell>{{ $item['item_name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $item['uom_name'] }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['max_quantity'], 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:input wire:model.live.debounce.500ms="items.{{ $index }}.quantity_return"
                                type="number" step="0.01" min="0" max="{{ $item['max_quantity'] }}"
                                class="w-24 text-right" size="sm" />
                        </flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['price'], 2) }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">{{ number_format((float)$item['tax'], 2) }}</flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums font-semibold">{{ number_format((float)$item['total'], 2) }}</flux:table.cell>
                        <flux:table.cell>
                            @if(count($items) > 1)
                                <flux:button variant="ghost" size="xs" icon="trash" class="text-red-500" x-on:click="$wire.set('pendingRemoveIndex', {{ $index }}); $flux.modal('remove-line-confirmation').show()" />
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div class="mt-3 border-t border-zinc-200 dark:border-zinc-700 pt-3 flex justify-end text-sm">
            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-right font-semibold">
                <span>Subtotal:</span>
                <span class="tabular-nums">{{ number_format(collect($items)->sum(fn($i) => (float)$i['total'] - (float)$i['tax']), 2) }}</span>
                <span>Tax:</span>
                <span class="tabular-nums">{{ number_format(collect($items)->sum('tax'), 2) }}</span>
                <span class="text-lg">Grand Total:</span>
                <span class="tabular-nums text-lg">{{ number_format(collect($items)->sum('total'), 2) }}</span>
            </div>
        </div>
    </flux:card>

    {{-- Actions --}}
    <div class="flex gap-2 justify-end">
        <flux:button :href="route('sales.return.index.draft')" variant="ghost" wire:navigate>Cancel</flux:button>
        <flux:button wire:click="save" variant="filled">Save Draft</flux:button>
        <flux:button variant="primary" icon="paper-airplane" x-on:click="$flux.modal('submit-confirmation').show()">Submit for Approval</flux:button>
    </div>

    {{-- Remove Line Confirmation Modal --}}
    <flux:modal name="remove-line-confirmation" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Remove Item</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Are you sure you want to remove this item from the return?</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('remove-line-confirmation').close()">Back</flux:button>
                <flux:button variant="danger" wire:click="removePendingLine" x-on:click="$flux.modal('remove-line-confirmation').close()">Yes, Remove</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Submit Confirmation Modal --}}
    <flux:modal name="submit-confirmation" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Submit for Approval</flux:heading>
                <flux:text class="mt-2 text-zinc-400">Submit this return for approval? It cannot be edited after submission.</flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" x-on:click="$flux.modal('submit-confirmation').close()">Back</flux:button>
                <flux:button variant="primary" wire:click="submit" x-on:click="$flux.modal('submit-confirmation').close()">Yes, Submit</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
