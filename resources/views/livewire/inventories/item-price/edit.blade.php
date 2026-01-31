<flux:modal name="edit-item-price" class="w-full max-w-2xl">
    <form wire:submit="update">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Item Price</flux:heading>

            @if($this->threshold > 0)
                <flux:callout icon="exclamation-triangle" variant="warning">
                    <flux:callout.heading>Approval Required</flux:callout.heading>
                    <flux:callout.text>
                        Price changes exceeding {{ number_format($this->threshold, 2) }}% will be submitted for manager approval.
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="space-y-6">
                <flux:input
                    label="Item"
                    :value="$itemPrice?->item?->code . ' - ' . $itemPrice?->item?->name"
                    readonly
                    disabled
                />

                <flux:input
                    label="Category Price"
                    :value="$itemPrice?->categoryPrice?->code . ' - ' . $itemPrice?->categoryPrice?->name"
                    readonly
                    disabled
                />

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
                    x-on:click="$flux.modal('edit-item-price').close()"
                >
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Update
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
