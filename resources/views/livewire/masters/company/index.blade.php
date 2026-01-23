<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Companies</flux:heading>
        @can('create company')
            <flux:button variant="primary" wire:click="$dispatch('cmw.master.company.create.open')">
                <flux:icon.plus class="size-5" />
                Add Company
            </flux:button>
        @endcan
    </div>

    <flux:separator class="my-4" />

    <flux:card>
        <livewire:masters.company.index-data-table />
    </flux:card>

    @can('create company')
        <livewire:masters.company.create />
    @endcan

    @can('edit company')
        <livewire:masters.company.edit />
    @endcan

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-company-confirmation">
        <flux:heading>Delete Company</flux:heading>
        <flux:subheading>Are you sure you want to delete this company? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer />
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-company-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
