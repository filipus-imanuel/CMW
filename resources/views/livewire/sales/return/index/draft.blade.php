<div>
    <flux:heading size="xl" class="mb-6">Sales Return - Draft</flux:heading>

    <div class="flex items-center justify-between mb-6">
        <flux:text>Manage your draft sales returns</flux:text>
        @can('create sales return')
            <flux:button :href="route('sales.return.create')" variant="primary" icon="plus" wire:navigate>
                Create Return
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <livewire:sales.return.index.draft-data-table />
    </flux:card>
</div>
