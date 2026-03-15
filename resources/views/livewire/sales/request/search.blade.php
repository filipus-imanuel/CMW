<div>
    <div class="mb-6">
        <flux:button :href="route('sales.request.edit', $orderId)" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Edit
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-2">Search & Add Items</flux:heading>
    <flux:subheading class="mb-6">
        {{ $order->code_request }} · {{ $order->partner?->name }} · {{ $order->itemCategory?->name }}
    </flux:subheading>

    <flux:card class="space-y-6">
        {{-- Filters --}}
        <div class="flex flex-wrap gap-4 items-end">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by code or name..."
                icon="magnifying-glass"
                class="w-72"
            />

            <flux:select wire:model.live="typeFilter" label="Item Type" class="w-48">
                <option value="">All Types</option>
                <option value="RAW_MATERIAL">Raw Material</option>
                <option value="WORK_IN_PROCESS">Work In Process</option>
                <option value="FINISHED_GOOD">Finished Good</option>
                <option value="SPARE_PART">Spare Part</option>
            </flux:select>

            <flux:select wire:model.live="uomFilter" label="UOM" class="w-48">
                <option value="">All UOM</option>
                @foreach($dropdown_uom as $uom)
                    <option value="{{ $uom['value'] }}">{{ $uom['label'] }}</option>
                @endforeach
            </flux:select>

            <flux:button wire:click="resetFilters" variant="ghost" icon="arrow-path">
                Reset
            </flux:button>

            <flux:spacer />

            <flux:badge color="zinc" size="lg">
                {{ count($addedUomIds) }} item(s) in order
            </flux:badge>
        </div>

        {{-- Items Table --}}
        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-28">Actions</flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Item Name</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>UOM</flux:table.column>
                <flux:table.column class="text-center">Category</flux:table.column>
                <flux:table.column class="text-center">HET Price</flux:table.column>
                <flux:table.column class="text-center">Resolved Price</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->items as $itemUom)
                    @php
                        $price = $this->resolvedPrices[$itemUom->id] ?? null;
                    @endphp
                    <flux:table.row :key="$itemUom->id">
                        <flux:table.cell>
                            <div class="flex gap-1">
                                @if(in_array($itemUom->id, $addedUomIds))
                                    <flux:button variant="ghost" size="xs" icon="check" disabled>
                                        Added
                                    </flux:button>
                                @else
                                    <flux:button
                                        wire:click="addItem({{ $itemUom->id }})"
                                        variant="primary"
                                        size="xs"
                                        icon="plus"
                                    >
                                        Add
                                    </flux:button>
                                @endif
                                @can('edit item price')
                                    <flux:button
                                        wire:click="editPrice({{ $itemUom->id }})"
                                        variant="ghost"
                                        size="xs"
                                        icon="pencil-square"
                                        title="Edit Master Price"
                                    />
                                @endcan
                            </div>
                        </flux:table.cell>
                        <flux:table.cell variant="strong">{{ $itemUom->item?->code }}</flux:table.cell>
                        <flux:table.cell>{{ $itemUom->item?->name }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">
                                {{ str_replace('_', ' ', $itemUom->item?->type ?? '') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $itemUom->uom?->name }}
                            @if($itemUom->is_base)
                                <span class="ml-1 text-xs text-zinc-400">(base)</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-center">
                            @if($price && $price['category_price_code'])
                                <flux:badge size="sm" color="blue">
                                    {{ $price['category_price_code'] }}
                                </flux:badge>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums">
                            {{ $price ? number_format($price['het_price'], 2) : '-' }}
                        </flux:table.cell>
                        <flux:table.cell class="text-right tabular-nums" variant="strong">
                            {{ $price ? number_format($price['sell_price'], 2) : '-' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-400 py-8">
                            No items found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Edit Price Modal --}}
    <flux:modal name="edit-price" class="w-full max-w-2xl">
        <form wire:submit="updatePrice">
            <div class="space-y-6">
                <flux:heading size="lg">Edit Master Price</flux:heading>

                @if($this->threshold > 0)
                    <flux:callout icon="information-circle" color="blue">
                        <flux:callout.heading>Approval Required</flux:callout.heading>
                        <flux:callout.text>
                            Price changes exceeding {{ number_format($this->threshold, 2) }}% will be submitted for manager approval.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <div class="space-y-6">
                    <flux:input
                        label="Item"
                        :value="($editingItemPrice?->itemUom?->item?->code ?? '') . ' - ' . ($editingItemPrice?->itemUom?->item?->name ?? '')"
                        readonly
                        disabled
                    />

                    <flux:input
                        label="UOM"
                        :value="$editingItemPrice?->itemUom?->uom?->name ?? ''"
                        readonly
                        disabled
                    />

                    <flux:input
                        label="Category Price"
                        :value="($editingItemPrice?->categoryPrice?->code ?? '') . ' - ' . ($editingItemPrice?->categoryPrice?->name ?? '')"
                        readonly
                        disabled
                    />

                    <flux:input
                        label="Current Price"
                        :value="number_format((float) ($editingItemPrice?->price ?? 0), 2)"
                        readonly
                        disabled
                    />

                    <flux:input
                        wire:model="priceInputs.price"
                        type="number"
                        step="0.01"
                        min="0"
                        label="New Price"
                        badge="Required"
                        placeholder="0.00"
                        :error="$errors->first('priceInputs.price')"
                    />

                    <flux:textarea
                        wire:model="priceInputs.remarks"
                        label="Remarks"
                        placeholder="Enter remarks"
                        rows="3"
                        :error="$errors->first('priceInputs.remarks')"
                    />
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button
                        type="button"
                        variant="ghost"
                        x-on:click="$flux.modal('edit-price').close()"
                    >
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Update Price
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
