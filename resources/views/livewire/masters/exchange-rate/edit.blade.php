<flux:modal name="edit-exchange-rate" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Edit Exchange Rate</flux:heading>

    <flux:callout color="amber" icon="exclamation-triangle">
        <flux:callout.heading>Note</flux:callout.heading>
        <flux:callout.text>Currency pair and effective date cannot be changed. Only the rate and remarks can be modified. The reciprocal rate will be automatically updated.</flux:callout.text>
    </flux:callout>

    <form wire:submit="update" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="inputs.from_currency_id" label="From Currency" badge="Required" disabled>
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="inputs.to_currency_id" label="To Currency" badge="Required" disabled>
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.effective_date" label="Effective Date" badge="Required" type="date" disabled />
            <flux:input wire:model="inputs.rate" label="Rate" badge="Required" type="number" step="0.000001" min="0" />
        </div>

        <flux:textarea wire:model="inputs.remarks" label="Remarks" placeholder="Enter remarks" rows="3" />

        <div class="flex items-center gap-4">
            <flux:switch wire:model="inputs.is_active" label="Active" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="submit" variant="primary">Update</flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-exchange-rate').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
