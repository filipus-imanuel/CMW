<flux:modal name="create-item-price" class="w-full max-w-2xl">
    <form wire:submit="store">
        <div class="space-y-6">
            <flux:heading size="lg">Create Item Price</flux:heading>

            <div class="space-y-6">
                <flux:select
                    wire:model="inputs.item_uom_id"
                    label="Item UOM"
                    badge="Required"
                    placeholder="Select item UOM"
                    searchable
                    :error="$errors->first('inputs.item_uom_id')"
                >
                    @foreach($dropdown_item_uoms as $itemUom)
                        <flux:select.option value="{{ $itemUom['value'] }}">{{ $itemUom['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select
                    wire:model="inputs.category_price_id"
                    label="Category Price"
                    badge="Required"
                    placeholder="Select category"
                    searchable
                    :error="$errors->first('inputs.category_price_id')"
                >
                    @foreach($dropdown_category_prices as $category)
                        <flux:select.option value="{{ $category['value'] }}">{{ $category['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="inputs.price"
                    type="number"
                    step="0.01"
                    min="0"
                    label="Price"
                    badge="Required"
                    placeholder="0.00"
                    :error="$errors->first('inputs.price')"
                />

                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    placeholder="Enter remarks"
                    rows="3"
                    :error="$errors->first('inputs.remarks')"
                />

                <flux:switch
                    wire:model="inputs.is_active"
                    label="Active"
                />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button
                    type="button"
                    variant="ghost"
                    x-on:click="$flux.modal('create-item-price').close()"
                >
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
