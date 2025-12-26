<flux:modal name="create-credit-term" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Create Credit Term</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="inputs.code" 
                        label="Code" 
                        badge="Required"
                        placeholder="e.g., NET30"
                        maxlength="50"
                    />

                    <flux:input 
                        wire:model="inputs.days" 
                        type="number"
                        label="Days" 
                        badge="Required"
                        placeholder="e.g., 30"
                        min="0"
                        max="365"
                    />
                </div>

                <flux:input 
                    wire:model="inputs.name" 
                    label="Name" 
                    badge="Required"
                    placeholder="e.g., Net 30 Days"
                    maxlength="100"
                />

                <flux:select 
                    wire:model="inputs.partner_address_id" 
                    label="Partner Address"
                    placeholder="Select partner address (optional)"
                    searchable
                >
                    @foreach($partner_addresses as $address)
                        <flux:select.option value="{{ $address['value'] }}">{{ $address['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea 
                    wire:model="inputs.description" 
                    label="Description"
                    rows="3"
                    placeholder="Enter description (optional)"
                    maxlength="500"
                />

                <flux:textarea 
                    wire:model="inputs.remarks" 
                    label="Remarks"
                    rows="3"
                    placeholder="Enter remarks (optional)"
                    maxlength="500"
                />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-credit-term').close()">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
