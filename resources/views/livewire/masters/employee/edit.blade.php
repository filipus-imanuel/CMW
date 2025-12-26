<flux:modal name="edit-employee" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Employee</flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="inputs.code" 
                        label="Code" 
                        badge="Required"
                        placeholder="e.g., EMP001"
                        maxlength="50"
                    />

                    <flux:select 
                        wire:model="inputs.position_id" 
                        label="Position"
                        badge="Required"
                        placeholder="Select position"
                        searchable
                    >
                        @foreach($positions as $position)
                            <flux:select.option value="{{ $position['value'] }}">{{ $position['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input 
                    wire:model="inputs.name" 
                    label="Name" 
                    badge="Required"
                    placeholder="e.g., John Doe"
                    maxlength="100"
                />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="inputs.email" 
                        type="email"
                        label="Email"
                        placeholder="e.g., john@example.com"
                        maxlength="100"
                    />

                    <flux:input 
                        wire:model="inputs.phone" 
                        label="Phone"
                        placeholder="e.g., +62 812-3456-7890"
                        maxlength="20"
                    />
                </div>

                <flux:textarea 
                    wire:model="inputs.address" 
                    label="Address"
                    rows="3"
                    placeholder="Enter address (optional)"
                    maxlength="500"
                />

                <flux:textarea 
                    wire:model="inputs.remarks" 
                    label="Remarks"
                    rows="3"
                    placeholder="Enter remarks (optional)"
                    maxlength="500"
                />

                <flux:switch 
                    wire:model="inputs.is_active" 
                    label="Active"
                />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('edit-employee').close()">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Update
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
