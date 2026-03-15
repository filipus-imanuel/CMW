<flux:modal name="create-payment-method" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Create Payment Method</flux:heading>

    <form wire:submit="store" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.code" label="Code" badge="Required" placeholder="Enter code" maxlength="50" />
            <flux:input wire:model="inputs.name" label="Name" badge="Required" placeholder="Enter name" maxlength="100" />
        </div>

        <flux:textarea wire:model="inputs.remarks" label="Remarks" placeholder="Enter remarks" rows="3" />

        <div class="flex items-center gap-4">
            <flux:switch wire:model="inputs.is_active" label="Active" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button type="submit" variant="primary">Save</flux:button>
            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-payment-method').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
