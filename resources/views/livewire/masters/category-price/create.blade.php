<flux:modal name="create-category-price" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Create Category Price</flux:heading>

            <div class="space-y-6">
                <flux:input
                    wire:model="inputs.code"
                    label="Code"
                    badge="Required"
                    placeholder="e.g., VIP"
                    maxlength="50"
                    :error="$errors->first('inputs.code')"
                />

                <flux:input
                    wire:model="inputs.name"
                    label="Name"
                    badge="Required"
                    placeholder="e.g., VIP Customer"
                    maxlength="100"
                    :error="$errors->first('inputs.name')"
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
                    x-on:click="$flux.modal('create-category-price').close()"
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
