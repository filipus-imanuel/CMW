<flux:modal name="edit-item-category" class="w-full max-w-2xl">
    <form wire:submit="update">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Item Category</flux:heading>

            @if($itemCategory?->is_edit_locked)
                <flux:callout color="amber" icon="exclamation-triangle">
                    This item category is locked and cannot be edited.
                </flux:callout>
            @endif

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.code"
                        label="Code"
                        badge="Required"
                        placeholder="e.g., CAT-RM"
                        :error="$errors->first('inputs.code')"
                        :disabled="$itemCategory?->is_edit_locked"
                    />

                    <flux:input
                        wire:model="inputs.name"
                        label="Name"
                        badge="Required"
                        placeholder="e.g., Raw Material"
                        :error="$errors->first('inputs.name')"
                        :disabled="$itemCategory?->is_edit_locked"
                    />
                </div>

                <flux:pillbox
                    wire:model="inputs.companies"
                    label="Companies"
                    badge="Required"
                    multiple
                    searchable
                    placeholder="Select companies..."
                    :disabled="$itemCategory?->is_edit_locked"
                >
                    @foreach($dropdown_data['companies'] ?? [] as $company)
                        <flux:pillbox.option value="{{ $company['value'] }}">
                            {{ $company['label'] }}
                        </flux:pillbox.option>
                    @endforeach
                </flux:pillbox>
                @error('inputs.companies')
                    <div class="text-sm text-red-600">{{ $message }}</div>
                @enderror

                <flux:textarea
                    wire:model="inputs.remarks"
                    label="Remarks"
                    placeholder="Optional notes about this category"
                    rows="3"
                    :error="$errors->first('inputs.remarks')"
                    :disabled="$itemCategory?->is_edit_locked"
                />

                <flux:switch
                    wire:model="inputs.is_active"
                    label="Active"
                    :disabled="$itemCategory?->is_edit_locked"
                />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-item-category').close()">Cancel</flux:button>
                <flux:button type="submit" variant="primary" :disabled="$itemCategory?->is_edit_locked">Update</flux:button>
            </div>
        </div>
    </form>
</flux:modal>
