<flux:modal name="create-exchange-rate" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Create Exchange Rate</flux:heading>

    <flux:callout color="blue" icon="information-circle">
        <flux:callout.text>A reciprocal rate will be automatically created. For example, if you set USD → IDR = 15,500, an IDR → USD rate of approximately 0.000065 will be created.</flux:callout.text>
    </flux:callout>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="inputs.from_currency_id" label="From Currency" badge="Required">
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="inputs.to_currency_id" label="To Currency" badge="Required">
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:date-picker wire:model="inputs.effective_date" label="Effective Date" badge="Required" />
            <flux:input wire:model="inputs.rate" label="Rate" badge="Required" type="number" step="0.000001" min="0" />
        </div>

        <flux:textarea wire:model="inputs.remarks" label="Remarks" placeholder="Enter remarks" rows="3" />

        <div class="flex items-center gap-4">
            <flux:switch wire:model="inputs.is_active" label="Active" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="submit" variant="primary">Save</flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-exchange-rate').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
