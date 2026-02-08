<div>
    <flux:heading size="xl" class="mb-6">Sales Requests - Draft</flux:heading>

    <div class="flex items-center justify-between mb-6">
        <flux:text>Manage your draft sales requests</flux:text>
        @can('create sales request')
            <flux:button :href="route('sales.request.create')" variant="primary" icon="plus" wire:navigate>
                Create Sales Request
            </flux:button>
        @endcan
    </div>

    <flux:card>
        <livewire:sales.request.index.init-data-table />
    </flux:card>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-sales-request-confirmation">
        <flux:heading>Delete Sales Request</flux:heading>
        <flux:subheading>Are you sure you want to delete this sales request? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-sales-request-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
