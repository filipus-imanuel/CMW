<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Employees</flux:heading>
        
        @can('create employee')
        <flux:button 
            icon="plus" 
            variant="primary"
            wire:click="$dispatch('cmw.master.employee.create.open')"
        >
            Create Employee
        </flux:button>
        @endcan
    </div>

    {{-- DataTable --}}
    <flux:card>
        <livewire:masters.employee.index-data-table />
    </flux:card>

    {{-- Modals --}}
    <livewire:masters.employee.create />
    <livewire:masters.employee.edit />

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-employee-confirmation">
        <flux:heading>Delete Employee</flux:heading>
        <flux:subheading>Are you sure you want to delete this employee? This action cannot be undone.</flux:subheading>

        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('delete-employee-confirmation').close()">Cancel</flux:button>
        </div>
    </flux:modal>
</div>
