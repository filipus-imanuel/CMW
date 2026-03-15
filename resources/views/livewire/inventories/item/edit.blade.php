<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('inventories.items.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Items
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Edit Item &mdash; {{ $item->code }}</flux:heading>

    <form wire:submit="update">
        {{-- Item Details --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Item Details</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.code"
                        label="Code"
                        badge="Required"
                        placeholder="e.g., ITM-001"
                        maxlength="50"
                    />

                    <flux:select
                        wire:model="inputs.type"
                        label="Type"
                        badge="Required"
                    >
                        <flux:select.option value="RAW_MATERIAL">Raw Material</flux:select.option>
                        <flux:select.option value="WORK_IN_PROCESS">Work In Process</flux:select.option>
                        <flux:select.option value="FINISHED_GOOD">Finished Good</flux:select.option>
                        <flux:select.option value="SPARE_PART">Spare Part</flux:select.option>
                    </flux:select>
                </div>

                <flux:select
                    wire:model="inputs.item_category_id"
                    label="Category"
                    placeholder="Select Category"
                    searchable
                >
                    @foreach($dropdown_item_category as $category)
                        <flux:select.option value="{{ $category['value'] }}">{{ $category['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="inputs.name"
                    label="Name"
                    badge="Required"
                    placeholder="e.g., Product Name"
                    maxlength="100"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.cost_price"
                        type="number"
                        step="0.01"
                        label="Cost Price"
                        badge="Required"
                        placeholder="0.00"
                    />

                    <flux:input
                        wire:model="inputs.sell_price"
                        type="number"
                        step="0.01"
                        label="Sell Price"
                        badge="Required"
                        placeholder="0.00"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.min_stock"
                        type="number"
                        step="0.0001"
                        label="Minimum Stock"
                        placeholder="0.0000"
                    />

                    <flux:input
                        wire:model="inputs.max_stock"
                        type="number"
                        step="0.0001"
                        label="Maximum Stock"
                        placeholder="0.0000"
                    />
                </div>

                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    rows="3"
                    placeholder="Enter remarks (optional)"
                    maxlength="500"
                />

                <flux:switch
                    wire:model="inputs.is_active"
                    label="Active"
                />
            </div>
        </flux:card>

        {{-- Warehouse Assignments --}}
        <flux:card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Warehouse Assignments</flux:heading>
                <flux:button type="button" icon="plus" size="sm" variant="primary" wire:click="addWarehouseRow">
                    Add Warehouse
                </flux:button>
            </div>

            @if(count($item_warehouses) > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Warehouse</flux:table.column>
                        <flux:table.column class="text-center w-20">Actions</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($item_warehouses as $w => $whRow)
                            <flux:table.row wire:key="wh-row-{{ $w }}">
                                <flux:table.cell>
                                    <flux:select
                                        wire:model.live="item_warehouses.{{ $w }}.warehouse_id"
                                        placeholder="Select Warehouse"
                                        size="sm"
                                        searchable
                                    >
                                        @foreach($dropdown_warehouses as $wh)
                                            <flux:select.option value="{{ $wh['value'] }}">{{ $wh['label'] }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    @error("item_warehouses.{$w}.warehouse_id")
                                        <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                    @enderror
                                </flux:table.cell>
                                <flux:table.cell class="text-center">
                                    <flux:button
                                        type="button"
                                        variant="danger"
                                        icon="trash"
                                        size="xs"
                                        wire:click="removeWarehouseRow({{ $w }})"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                {{-- Default Warehouse --}}
                @if(count($this->assignedWarehouseIds) > 0)
                    <div class="mt-4">
                        <flux:select
                            wire:model="inputs.default_warehouse_id"
                            label="Default Warehouse"
                            placeholder="Select Default Warehouse (Optional)"
                            searchable
                        >
                            @foreach($dropdown_warehouses as $wh)
                                @if(in_array($wh['value'], $this->assignedWarehouseIds))
                                    <flux:select.option value="{{ $wh['value'] }}">{{ $wh['label'] }}</flux:select.option>
                                @endif
                            @endforeach
                        </flux:select>
                    </div>
                @endif
            @else
                <flux:callout variant="info" icon="information-circle">
                    No warehouses assigned. Click "Add Warehouse" to assign warehouses to this item.
                </flux:callout>
            @endif
        </flux:card>

        {{-- Units of Measure --}}
        <flux:card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">Units of Measure (UOM)</flux:heading>
                <flux:button type="button" icon="plus" size="sm" variant="primary" wire:click="addUomRow">
                    Add UOM
                </flux:button>
            </div>

            @error('uoms')
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">{{ $message }}</flux:callout>
            @enderror

            @if(count($uoms) > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="text-center">Base</flux:table.column>
                        <flux:table.column>UOM</flux:table.column>
                        <flux:table.column class="text-center">Conversion Rate</flux:table.column>
                        <flux:table.column>Remarks</flux:table.column>
                        <flux:table.column class="text-center">Actions</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($uoms as $u => $uomRow)
                            <flux:table.row wire:key="uom-row-{{ $u }}">
                                <flux:table.cell class="text-center">
                                    <input
                                        type="radio"
                                        name="base_uom"
                                        {{ $uomRow['is_base'] ? 'checked' : '' }}
                                        wire:click="setBaseUom({{ $u }})"
                                        class="accent-blue-600"
                                    />
                                </flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $selectedUomIds = collect($uoms)->pluck('uom_id')->filter()->all();
                                    @endphp
                                    <flux:select
                                        wire:model.live="uoms.{{ $u }}.uom_id"
                                        placeholder="Select UOM"
                                        size="sm"
                                        searchable
                                    >
                                        @foreach($dropdown_uom as $uom)
                                            <flux:select.option
                                                value="{{ $uom['value'] }}"
                                                :disabled="in_array($uom['value'], $selectedUomIds) && $uom['value'] != ($uomRow['uom_id'] ?? '')"
                                            >{{ $uom['label'] }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    @error("uoms.{$u}.uom_id")
                                        <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                    @enderror
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:input
                                        wire:model="uoms.{{ $u }}.conversion_rate"
                                        type="number"
                                        step="0.0001"
                                        placeholder="1.0000"
                                        size="sm"
                                        :disabled="$uomRow['is_base']"
                                    />
                                    @error("uoms.{$u}.conversion_rate")
                                        <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                    @enderror
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:input
                                        wire:model="uoms.{{ $u }}.remarks"
                                        placeholder="Optional"
                                        size="sm"
                                    />
                                </flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if(count($uoms) > 1)
                                        <flux:button
                                            type="button"
                                            variant="danger"
                                            icon="trash"
                                            size="xs"
                                            wire:click="removeUomRow({{ $u }})"
                                        />
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:callout variant="info" icon="information-circle">
                    At least one UOM is required. Click "Add UOM" to add a unit of measure.
                </flux:callout>
            @endif
        </flux:card>

        {{-- Category Prices per UOM --}}
        @foreach($uoms as $u => $uomRow)
            <flux:card class="mb-6" wire:key="uom-prices-{{ $u }}">
                <flux:heading size="lg" class="mb-4">
                    Prices &mdash; {{ collect($dropdown_uom)->firstWhere('value', $uomRow['uom_id'])['label'] ?? $uomRow['uom_label'] ?? 'UOM #'.($u + 1) }}
                    @if($uomRow['is_base'])
                        <flux:badge variant="primary" size="sm" class="ml-2">Base</flux:badge>
                    @endif
                </flux:heading>

                @if($this->threshold > 0)
                    <flux:callout color="blue" icon="information-circle" class="mb-4">
                        Price changes exceeding {{ number_format($this->threshold, 2) }}% will be submitted for approval instead of applied directly.
                    </flux:callout>
                @endif

                @if(!empty($uomRow['prices']))
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Category Price</flux:table.column>
                            <flux:table.column class="text-center">Price</flux:table.column>
                            <flux:table.column>Remarks</flux:table.column>
                            <flux:table.column class="text-center">Active</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($uomRow['prices'] as $p => $priceRow)
                                <flux:table.row wire:key="uom-{{ $u }}-price-{{ $p }}">
                                    <flux:table.cell>
                                        <flux:text class="font-medium">
                                            @if(!empty($priceRow['id']))
                                                {{ $priceRow['category_price_label'] }}
                                            @else
                                                {{ collect($dropdown_category_prices)->firstWhere('value', $priceRow['category_price_id'])['label'] ?? $priceRow['category_price_label'] ?? '-' }}
                                            @endif
                                        </flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:input
                                            wire:model="uoms.{{ $u }}.prices.{{ $p }}.price"
                                            type="number"
                                            step="0.01"
                                            placeholder="0.00"
                                            size="sm"
                                        />
                                        @error("uoms.{$u}.prices.{$p}.price")
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:input
                                            wire:model="uoms.{{ $u }}.prices.{{ $p }}.remarks"
                                            placeholder="Optional"
                                            size="sm"
                                        />
                                    </flux:table.cell>
                                    <flux:table.cell class="text-center">
                                        <flux:switch wire:model="uoms.{{ $u }}.prices.{{ $p }}.is_active" />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @else
                    <flux:callout variant="info" icon="information-circle">
                        No category prices configured. Category prices will appear here automatically.
                    </flux:callout>
                @endif
            </flux:card>
        @endforeach

        {{-- Footer Actions --}}
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button :href="route('inventories.items.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">Update</flux:button>
        </div>
    </form>
</div>
