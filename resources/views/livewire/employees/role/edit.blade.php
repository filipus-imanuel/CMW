<div>
    <div class="mb-6">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('employees.roles.index') }}" wire:navigate>
            Back to Roles
        </flux:button>
    </div>

    {{-- Header --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Edit Role &mdash; {{ $roleName }}</flux:heading>
            <flux:subheading>{{ $this->selectedCount }} of {{ $this->totalPermissions }} permissions
                selected</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="users" href="{{ route('employees.roles.user', $role->id) }}"
                wire:navigate>
                Manage Users
            </flux:button>
            <flux:button variant="primary" wire:click="update">
                Save Permissions
            </flux:button>
        </div>
    </div>

    {{-- Search & Bulk Actions --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search permissions..."
                clearable />
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" size="sm" wire:click="selectAll">Select All</flux:button>
            <flux:button variant="ghost" size="sm" wire:click="deselectAll">Deselect All</flux:button>
        </div>
    </div>

    {{-- Permission Matrix --}}
    <div class="space-y-4">
        @forelse($this->filteredModules as $moduleLabel => $resources)
            <flux:card class="overflow-hidden">
                {{-- Module Header --}}
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg">{{ $moduleLabel }}</flux:heading>
                    <div class="flex gap-2">
                        <flux:button variant="ghost" size="xs"
                            wire:click="selectAllModule('{{ $moduleLabel }}')">
                            Select All
                        </flux:button>
                        <flux:button variant="ghost" size="xs"
                            wire:click="deselectAllModule('{{ $moduleLabel }}')">
                            Deselect All
                        </flux:button>
                    </div>
                </div>

                {{-- Resources --}}
                <div class="space-y-4">
                    @foreach ($resources as $resource => $permissions)
                        <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold capitalize text-zinc-700 dark:text-zinc-300">
                                    {{ $resource }}
                                </span>
                                <div class="flex gap-1">
                                    <flux:button variant="ghost" size="xs"
                                        wire:click="selectAllResource('{{ $moduleLabel }}', '{{ $resource }}')">
                                        All
                                    </flux:button>
                                    <flux:button variant="ghost" size="xs"
                                        wire:click="deselectAllResource('{{ $moduleLabel }}', '{{ $resource }}')">
                                        None
                                    </flux:button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <flux:checkbox
                                            wire:click="togglePermission('{{ $permission['name'] }}')"
                                            :checked="in_array($permission['name'], $selectedPermissions)" />
                                        <span
                                            class="text-sm capitalize text-zinc-600 dark:text-zinc-400">{{ $permission['action'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @empty
            <flux:card>
                <div class="py-8 text-center text-zinc-500">
                    No permissions found matching "{{ $search }}"
                </div>
            </flux:card>
        @endforelse
    </div>

    {{-- Sticky Footer --}}
    <div class="sticky bottom-0 z-10 flex items-center justify-between p-4 mt-6 border rounded-lg bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm border-zinc-200 dark:border-zinc-700">
        <span class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ $this->selectedCount }} of {{ $this->totalPermissions }} permissions selected
        </span>
        <flux:button variant="primary" wire:click="update">
            Save Permissions
        </flux:button>
    </div>
</div>
