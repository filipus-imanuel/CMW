<flux:modal name="create-warehouse" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Create Warehouse</flux:heading>

            <div class="space-y-6">
                <flux:input
                    wire:model="inputs.code"
                    label="Code"
                    badge="Required"
                    placeholder="Enter code"
                    :error="$errors->first('inputs.code')"
                />

                <flux:input
                    wire:model="inputs.name"
                    label="Name"
                    badge="Required"
                    placeholder="Enter name"
                    :error="$errors->first('inputs.name')"
                />

                <flux:textarea
                    wire:model="inputs.address"
                    label="Address"
                    placeholder="Enter address"
                    rows="3"
                    :error="$errors->first('inputs.address')"
                />

                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    placeholder="Enter remarks"
                    rows="3"
                    :error="$errors->first('inputs.remarks')"
                />

                <flux:checkbox
                    wire:model="inputs.is_active"
                    label="Active"
                />
            </div>

            <div class="flex">
                <flux:spacer />
            <flux:button
                type="button"
                variant="ghost"
                x-on:click="$flux.modal('create-warehouse').close()"
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
