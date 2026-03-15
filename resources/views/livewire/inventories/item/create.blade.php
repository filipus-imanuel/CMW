<div>
    {{-- Back Button --}}
    <div class="mb-6">
        <flux:button :href="route('inventories.items.index')" variant="ghost" icon="arrow-left" wire:navigate>
            Back to Items
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Create Item</flux:heading>

    <form wire:submit="store">
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
                    badge="Required"
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
            <flux:heading size="lg" class="mb-4">Warehouse Assignments</flux:heading>

            @if(count($dropdown_warehouses) > 0)
                <flux:checkbox.group wire:model.live="item_warehouses" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($dropdown_warehouses as $wh)
                        <flux:checkbox
                            wire:key="wh-chk-{{ $wh['value'] }}"
                            value="{{ $wh['value'] }}"
                            label="{{ $wh['label'] }}"
                            description="{{ $wh['company_name'] }}"
                        />
                    @endforeach
                </flux:checkbox.group>

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
                    No warehouses available.
                </flux:callout>
            @endif
        </flux:card>

        {{-- Initial Stock (Qty Awal) --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Initial Stock (Qty Awal)</flux:heading>

            @if(count($this->assignedWarehouseIds) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->assignedWarehouseIds as $whId)
                        @php
                            $whData = collect($dropdown_warehouses)->firstWhere('value', $whId);
                        @endphp
                        @if($whData)
                            <div wire:key="init-stock-{{ $whId }}">
                                <flux:input
                                    wire:model="initial_stocks.{{ $whId }}.qty"
                                    type="number"
                                    step="0.01"
                                    label="{{ $whData['label'] }}"
                                    description="{{ $whData['company_name'] }}"
                                    placeholder="0.00"
                                    :badge="$this->baseUomLabel ?: null"
                                />
                                @error("initial_stocks.{$whId}.qty")
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <flux:callout variant="info" icon="information-circle">
                    Assign at least one warehouse to set initial stock.
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
                <div class="space-y-4">
                    @foreach($uoms as $u => $uomRow)
                        <div wire:key="uom-block-{{ $u }}" class="border border-zinc-700 rounded-lg overflow-hidden">
                            {{-- UOM Row --}}
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column class="text-center w-16">Base</flux:table.column>
                                    <flux:table.column>UOM</flux:table.column>
                                    <flux:table.column class="text-center">Conversion Rate</flux:table.column>
                                    <flux:table.column>Remarks</flux:table.column>
                                    <flux:table.column class="text-center w-20">Actions</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    <flux:table.row wire:key="uom-row-{{ $u }}">
                                        <flux:table.cell class="text-center">
                                            <input
                                                type="radio"
                                                wire:model.live="baseUomIndex"
                                                value="{{ $u }}"
                                                class="accent-blue-600 cursor-pointer"
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
                                </flux:table.rows>
                            </flux:table>

                            {{-- Nested Prices --}}
                            @if(!empty($uomRow['prices']))
                                <div class="border-t border-zinc-700 bg-zinc-900/40 px-4 py-3">
                                    <div class="flex items-center gap-2 mb-3">
                                        <flux:heading size="sm" class="text-zinc-400">
                                            Prices &mdash; {{ collect($dropdown_uom)->firstWhere('value', $uomRow['uom_id'])['label'] ?? 'UOM #'.($u + 1) }}
                                        </flux:heading>
                                        @if($uomRow['is_base'])
                                            <flux:badge variant="primary" size="sm">Base</flux:badge>
                                        @endif
                                    </div>
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
                                                        <flux:text class="font-medium">{{ $priceRow['category_price_label'] }}</flux:text>
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
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <flux:callout variant="info" icon="information-circle">
                    At least one UOM is required. Click "Add UOM" to add a unit of measure.
                </flux:callout>
            @endif
        </flux:card>

        {{-- Footer Actions --}}
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button :href="route('inventories.items.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save</flux:button>
        </div>
    </form>
</div>
