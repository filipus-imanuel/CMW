<div>
    <div class="mb-6">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('employees.roles.index') }}" wire:navigate>
            Back to Roles
        </flux:button>
    </div>

    {{-- Header --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Manage Users &mdash; {{ $role->name }}</flux:heading>
            <flux:subheading>{{ $this->selectedCount }} {{ Str::plural('user', $this->selectedCount) }}
                assigned</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="shield-check" href="{{ route('employees.roles.edit', $role->id) }}"
                wire:navigate>
                Manage Permissions
            </flux:button>
            <flux:button variant="primary" wire:click="syncUsers">
                Save Assignments
            </flux:button>
        </div>
    </div>

    {{-- Search & Bulk Actions --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search users by name, email, or department..." clearable />
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" size="sm" wire:click="selectAll">Select All</flux:button>
            <flux:button variant="ghost" size="sm" wire:click="deselectAll">Deselect All</flux:button>
        </div>
    </div>

    {{-- User List --}}
    <div class="space-y-2">
        @forelse($this->filteredUsers as $user)
            <flux:card
                class="flex items-center gap-4 cursor-pointer transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ in_array($user->id, $selectedUsers) ? 'ring-2 ring-blue-500/50 bg-blue-50/50 dark:bg-blue-950/20' : '' }}"
                wire:click="toggleUser({{ $user->id }})" wire:key="user-{{ $user->id }}">
                <flux:checkbox :checked="in_array($user->id, $selectedUsers)" class="pointer-events-none" />

                <div class="flex items-center justify-center w-10 h-10 text-sm font-semibold rounded-full bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 shrink-0">
                    {{ $user->initials() }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                </div>

                <div class="items-center hidden gap-2 sm:flex">
                    @if ($user->department)
                        <flux:badge size="sm" variant="outline">{{ $user->department->name }}</flux:badge>
                    @endif
                    @foreach ($user->roles as $userRole)
                        <flux:badge size="sm" color="blue">{{ $userRole->name }}</flux:badge>
                    @endforeach
                </div>
            </flux:card>
        @empty
            <flux:card>
                <div class="py-8 text-center text-zinc-500">
                    @if ($search)
                        No users found matching "{{ $search }}"
                    @else
                        No active users available
                    @endif
                </div>
            </flux:card>
        @endforelse
    </div>

    {{-- Sticky Footer --}}
    <div class="sticky bottom-0 z-10 flex items-center justify-between p-4 mt-6 border rounded-lg bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm border-zinc-200 dark:border-zinc-700">
        <span class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ $this->selectedCount }} {{ Str::plural('user', $this->selectedCount) }} assigned to
            {{ $role->name }}
        </span>
        <flux:button variant="primary" wire:click="syncUsers">
            Save Assignments
        </flux:button>
    </div>
</div>
