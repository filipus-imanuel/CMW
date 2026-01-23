<flux:modal name="create-company" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Create Company</flux:heading>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.code" label="Code" badge="Required" placeholder="Enter code" maxlength="50" />
            <flux:input wire:model="inputs.name" label="Name" badge="Required" placeholder="Enter name" maxlength="100" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.sales_limit" label="Sales Limit" type="number" step="1" min="0" max="999999999999" />
            <flux:select wire:model="inputs.currency_id" label="Currency" badge="Required">
                <flux:select.option value="">-- Select Currency --</flux:select.option>
                @foreach ($dropdown_currency as $currency)
                    <flux:select.option value="{{ $currency['value'] }}">{{ $currency['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
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
