<flux:modal name="create-customer" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Create Customer</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="inputs.code" 
                        label="Code" 
                        badge="Required"
                        placeholder="e.g., CUST001"
                        maxlength="50"
                    />

                    <flux:input 
                        wire:model="inputs.name" 
                        label="Name" 
                        badge="Required"
                        placeholder="e.g., ABC Customer"
                        maxlength="100"
                    />
                </div>

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
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-customer').close()">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
