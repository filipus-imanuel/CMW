<flux:modal name="edit-uom-conversion" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Edit UOM Conversion</flux:heading>

            <div class="space-y-4">
                <flux:callout variant="info" icon="information-circle">
                    Define how one unit of measurement converts to another. For example: 1 Dozen = 12 Pieces
                </flux:callout>

                <div class="grid grid-cols-2 gap-4">
                    <flux:select 
                        wire:model="inputs.from_uom_id" 
                        label="From UOM"
                        badge="Required"
                        placeholder="Select UOM"
                        searchable
                    >
                        @foreach($dropdown_from_uom as $uom)
                            <flux:select.option value="{{ $uom['value'] }}">{{ $uom['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select 
                        wire:model="inputs.to_uom_id" 
                        label="To UOM"
                        badge="Required"
                        placeholder="Select UOM"
                        searchable
                    >
                        @foreach($dropdown_to_uom as $uom)
                            <flux:select.option value="{{ $uom['value'] }}">{{ $uom['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input 
                    wire:model="inputs.conversion_rate" 
                    type="number"
                    step="0.000001"
                    label="Conversion Rate" 
                    badge="Required"
                    placeholder="e.g., 12 or 0.001"
                    description="How many 'To UOM' units equal 1 'From UOM' unit"
                />

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
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-uom-conversion').close()">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Update
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
