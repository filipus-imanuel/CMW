<flux:modal name="create-company" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Create Company</flux:heading>

    <form wire:submit="store" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.code" label="Code" badge="Required" placeholder="Enter code" maxlength="50" />
            <flux:input wire:model="inputs.name" label="Name" badge="Required" placeholder="Enter name" maxlength="100" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.sales_limit" label="Sales Limit" type="number" step="1" min="0" max="999999999999" />
            <flux:input wire:model="inputs.payment_code" label="Payment Code" placeholder="e.g. 01" maxlength="2" description="2-digit code used in payment number generation" />
        </div>

        <flux:separator text="Bank Information" />

        <div class="grid grid-cols-3 gap-4">
            <flux:input wire:model="inputs.bank_name" label="Bank Name" placeholder="e.g. BCA" maxlength="100" />
            <flux:input wire:model="inputs.bank_account_name" label="Account Name" placeholder="e.g. PT INDONUSA" maxlength="100" />
            <flux:input wire:model="inputs.bank_account_number" label="Account Number" placeholder="e.g. 555059018" maxlength="50" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="inputs.currency_id" label="Currency" badge="Required">
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <flux:radio.group wire:model.live="inputs.tax_mode" label="Default Tax Mode" variant="segmented">
                    <flux:radio value="INCLUDE" label="Include" />
                    <flux:radio value="EXCLUDE" label="Exclude" />
                    <flux:radio value="NONE" label="No Tax" />
                </flux:radio.group>
                @error('inputs.tax_mode')
                    <flux:text class="text-sm text-red-500 mt-1">{{ $message }}</flux:text>
                @enderror
            </div>

            @if ($inputs['tax_mode'] !== 'NONE')
                <flux:select wire:model="inputs.tax_id" label="Default Tax" badge="Required">
                    <flux:select.option value="">-- Select Tax --</flux:select.option>
                    @foreach ($dropdown_taxes as $tax)
                        <flux:select.option value="{{ $tax['value'] }}">{{ $tax['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        <flux:textarea wire:model="inputs.remarks" label="Remarks" placeholder="Enter remarks" rows="3" />

        <div class="flex items-center gap-4">
            <flux:switch wire:model="inputs.is_active" label="Active" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="submit" variant="primary">Save</flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-company').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
