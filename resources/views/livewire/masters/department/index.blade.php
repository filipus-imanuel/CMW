<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Departments</flux:heading>

        @can('create department')
        <flux:button
            wire:click="$dispatch('cmw.master.department.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Department
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.department.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.department.create />
    <livewire:masters.department.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-department-confirmation">
        <flux:heading>Delete Department</flux:heading>
        <flux:subheading>Are you sure you want to delete this department? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-department-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
