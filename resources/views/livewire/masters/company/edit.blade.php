<flux:modal name="edit-company" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Edit Company</flux:heading>

    @if ($company?->is_edit_locked)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Edit Locked</flux:callout.heading>
            <flux:callout.text>This company is locked and cannot be edited.</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.code" label="Code" badge="Required" placeholder="Enter code" maxlength="50" :disabled="$company?->is_edit_locked" />
            <flux:input wire:model="inputs.name" label="Name" badge="Required" placeholder="Enter name" maxlength="100" :disabled="$company?->is_edit_locked" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.sales_limit" label="Sales Limit" type="number" step="1" min="0" max="999999999999" :disabled="$company?->is_edit_locked" />
            <flux:select wire:model="inputs.currency_id" label="Currency" badge="Required" disabled>
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <flux:radio.group wire:model.live="inputs.tax_mode" label="Default Tax Mode" variant="segmented" :disabled="$company?->is_edit_locked">
                    <flux:radio value="INCLUDE" label="Include" />
                    <flux:radio value="EXCLUDE" label="Exclude" />
                    <flux:radio value="NONE" label="No Tax" />
                </flux:radio.group>
                @error('inputs.tax_mode')
                    <flux:text class="text-sm text-red-500 mt-1">{{ $message }}</flux:text>
                @enderror
            </div>

            @if ($inputs['tax_mode'] !== 'NONE')
                <flux:select wire:model="inputs.tax_id" label="Default Tax" badge="Required" :disabled="$company?->is_edit_locked">
                    <flux:select.option value="">-- Select Tax --</flux:select.option>
                    @foreach ($dropdown_taxes as $tax)
                        <flux:select.option value="{{ $tax['value'] }}">{{ $tax['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        <flux:callout color="blue" icon="information-circle" class="text-sm">
            <flux:callout.text>Currency cannot be changed after company creation.</flux:callout.text>
        </flux:callout>

        <flux:textarea wire:model="inputs.remarks" label="Remarks" placeholder="Enter remarks" rows="3" :disabled="$company?->is_edit_locked" />

        <div class="flex items-center gap-4">
            <flux:switch wire:model="inputs.is_active" label="Active" :disabled="$company?->is_edit_locked" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            @unless ($company?->is_edit_locked)
                <flux:button type="submit" variant="primary">Update</flux:button>
            @endunless
            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-company').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
