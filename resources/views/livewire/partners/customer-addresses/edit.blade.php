<flux:modal name="edit-customer-address" class="w-full max-w-3xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Customer Address</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:select 
                        wire:model="inputs.partner_id" 
                        label="Customer"
                        badge="Required"
                        placeholder="Select customer"
                        searchable
                    >
                        @foreach($dropdown_customer as $partner)
                            <flux:select.option value="{{ $partner['value'] }}">{{ $partner['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input 
                        wire:model="inputs.label" 
                        label="Label" 
                        badge="Required"
                        placeholder="e.g., Head Office, Warehouse"
                        maxlength="100"
                    />
                </div>

                <flux:textarea 
                    wire:model="inputs.address" 
                    label="Address" 
                    badge="Required"
                    rows="3"
                    placeholder="Enter full address"
                    maxlength="500"
                />

                <div class="grid grid-cols-3 gap-4">
                    <flux:input 
                        wire:model="inputs.city" 
                        label="City"
                        placeholder="e.g., Jakarta"
                        maxlength="100"
                    />

                    <flux:input 
                        wire:model="inputs.phone" 
                        label="Phone"
                        placeholder="e.g., +62 21-123-4567"
                        maxlength="20"
                    />

                    <flux:input 
                        wire:model="inputs.contact_person" 
                        label="Contact Person"
                        placeholder="e.g., John Doe"
                        maxlength="100"
                    />
                </div>

                <flux:checkbox wire:model="inputs.is_default" label="Set as default address" />

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
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-customer-address').close()">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Update
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
