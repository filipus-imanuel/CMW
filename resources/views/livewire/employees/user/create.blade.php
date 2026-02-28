<div>
    <div class="mb-6">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('employees.users.index') }}" wire:navigate>
            Back to Users
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-6">Create User</flux:heading>

    <flux:card>
        <form wire:submit="store" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="inputs.name" label="Name" placeholder="Full name" required />

                <flux:input wire:model="inputs.email" label="Email" type="email" placeholder="user@example.com"
                    required />

                <flux:select wire:model="inputs.department_id" label="Department" placeholder="Select department..."
                    searchable clearable>
                    @foreach ($dropdown_department as $department)
                        <flux:select.option value="{{ $department['value'] }}">
                            {{ $department['label'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="inputs.phone" label="Phone" placeholder="Phone number" />

                <flux:input wire:model="inputs.password" label="Password" type="password"
                    placeholder="Minimum 8 characters" required />

                <flux:input wire:model="inputs.password_confirmation" label="Confirm Password" type="password"
                    placeholder="Confirm password" required />
            </div>

            <flux:separator />

            <div class="flex items-center gap-4">
                <flux:switch wire:model="inputs.is_active" label="Active" description="User can log in when active" />
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" href="{{ route('employees.users.index') }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button variant="primary" type="submit">
                    Create User
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
