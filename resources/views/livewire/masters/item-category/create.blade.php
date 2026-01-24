<flux:modal name="create-item-category" class="w-full max-w-2xl">
    <form wire:submit="store">
        <div class="space-y-6">
            <flux:heading size="lg">Create Item Category</flux:heading>

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="inputs.code"
                        label="Code"
                        badge="Required"
                        placeholder="e.g., CAT-RM"
                        :error="$errors->first('inputs.code')"
                    />

                    <flux:input
                        wire:model="inputs.name"
                        label="Name"
                        badge="Required"
                        placeholder="e.g., Raw Material"
                        :error="$errors->first('inputs.name')"
                    />
                </div>

                <flux:pillbox
                    wire:model="inputs.companies"
                    label="Companies"
                    badge="Required"
                    multiple
                    searchable
                    placeholder="Select companies..."
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
                />

                <flux:switch
                    wire:model="inputs.is_active"
                    label="Active"
                />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-item-category').close()">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </div>
    </form>
</flux:modal>
