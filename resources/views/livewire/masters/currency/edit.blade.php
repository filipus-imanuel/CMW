<flux:modal name="edit-currency" class="w-full max-w-2xl">
    <form wire:submit="update">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Currency</flux:heading>

            @if($currency?->is_edit_locked)
                <flux:callout color="amber" icon="exclamation-triangle">
                    This currency is locked and cannot be edited.
                </flux:callout>
            @endif

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.code"
                        label="Code"
                        badge="Required"
                        placeholder="e.g., USD, EUR"
                        :error="$errors->first('inputs.code')"
                        :disabled="$currency?->is_edit_locked"
                    />

                    <flux:input
                        wire:model="inputs.name"
                        label="Name"
                        badge="Required"
                        placeholder="e.g., US Dollar"
                        :error="$errors->first('inputs.name')"
                        :disabled="$currency?->is_edit_locked"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.symbol"
                        label="Symbol"
                        badge="Required"
                        placeholder="e.g., $, €, Rp"
                        :error="$errors->first('inputs.symbol')"
                        :disabled="$currency?->is_edit_locked"
                    />

                    <flux:select
                        wire:model="inputs.symbol_position"
                        label="Symbol Position"
                        badge="Required"
                        placeholder="Select position"
                        :error="$errors->first('inputs.symbol_position')"
                        :disabled="$currency?->is_edit_locked"
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
                    :disabled="$currency?->is_edit_locked"
                />

                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    placeholder="Enter remarks"
                    rows="3"
                    :error="$errors->first('inputs.remarks')"
                    :disabled="$currency?->is_edit_locked"
                />

                <flux:checkbox
                    wire:model="inputs.is_active"
                    label="Active"
                    :disabled="$currency?->is_edit_locked"
                />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button
                    type="button"
                    variant="ghost"
                    x-on:click="$flux.modal('edit-currency').close()"
                >
                    Cancel
                </flux:button>
                @if(!$currency?->is_edit_locked)
                    <flux:button type="submit" variant="primary">
                        Update
                    </flux:button>
                @endif
            </div>
        </div>
    </form>
</flux:modal>
