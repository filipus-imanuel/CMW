<flux:modal name="create-currency" class="w-full max-w-2xl">
    <form wire:submit="store">
        <div class="space-y-6">
            <flux:heading size="lg">Create Currency</flux:heading>

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.code"
                        label="Code"
                        badge="Required"
                        placeholder="e.g., USD, EUR"
                        :error="$errors->first('inputs.code')"
                    />

                    <flux:input
                        wire:model="inputs.name"
                        label="Name"
                        badge="Required"
                        placeholder="e.g., US Dollar"
                        :error="$errors->first('inputs.name')"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.symbol"
                        label="Symbol"
                        badge="Required"
                        placeholder="e.g., $, €, Rp"
                        :error="$errors->first('inputs.symbol')"
                    />

                    <flux:select
                        wire:model="inputs.symbol_position"
                        label="Symbol Position"
                        badge="Required"
                        placeholder="Select position"
                        :error="$errors->first('inputs.symbol_position')"
                    >
                        @foreach($dropdown_symbol_position as $option)
                            <flux:select.option value="{{ $option['value'] }}">
                                {{ $option['label'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input
                    wire:model="inputs.rate"
                    type="number"
                    step="0.01"
                    min="0"
                    label="Exchange Rate"
                    badge="Required"
                    placeholder="Enter exchange rate"
                    :error="$errors->first('inputs.rate')"
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
                    x-on:click="$flux.modal('create-currency').close()"
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
