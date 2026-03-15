<flux:modal name="edit-payment-method" class="w-full max-w-xl space-y-6">
    <flux:heading size="lg">Edit Payment Method</flux:heading>

    @if ($paymentMethod?->is_edit_locked)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Edit Locked</flux:callout.heading>
            <flux:callout.text>This payment method is locked and cannot be edited.</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="inputs.code" label="Code" badge="Required" placeholder="Enter code" maxlength="50" :disabled="$paymentMethod?->is_edit_locked" />
            <flux:input wire:model="inputs.name" label="Name" badge="Required" placeholder="Enter name" maxlength="100" :disabled="$paymentMethod?->is_edit_locked" />
        </div>

        <flux:textarea wire:model="inputs.remarks" label="Remarks" placeholder="Enter remarks" rows="3" :disabled="$paymentMethod?->is_edit_locked" />

        <div class="flex items-center gap-4">
            <flux:switch wire:model="inputs.is_active" label="Active" :disabled="$paymentMethod?->is_edit_locked" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            @unless ($paymentMethod?->is_edit_locked)
                <flux:button type="submit" variant="primary">Update</flux:button>
            @endunless
            <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-payment-method').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
