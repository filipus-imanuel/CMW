<flux:modal name="create-item" class="w-full max-w-3xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Create Item</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
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

                <flux:input 
                    wire:model="inputs.name" 
                    label="Name" 
                    badge="Required"
                    placeholder="e.g., Product Name"
                    maxlength="100"
                />

                <flux:select 
                    wire:model="inputs.uom_id" 
                    label="Unit of Measure (UOM)"
                    badge="Required"
                    placeholder="Select UOM"
                    searchable
                >
                    @foreach($dropdown_uom as $uom)
                        <flux:select.option value="{{ $uom['value'] }}">{{ $uom['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
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

                <div class="grid grid-cols-2 gap-4">
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

            <div class="flex">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-item').close()">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
