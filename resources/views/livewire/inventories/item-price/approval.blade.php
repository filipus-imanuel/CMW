<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Item Price Approvals</flux:heading>
    </div>

    <flux:card class="space-y-6">
        {{-- Filters --}}
        <div class="flex flex-wrap gap-4 items-end">
            <flux:select wire:model.live="statusFilter" label="Status" class="w-40">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </flux:select>

            <flux:select wire:model.live="categoryFilter" label="Category" class="w-48">
                <option value="">All Categories</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>

            <flux:date-picker wire:model.live="dateFrom" label="From" class="w-40" />

            <flux:date-picker wire:model.live="dateTo" label="To" class="w-40" />

            <flux:button wire:click="resetFilters" variant="ghost" icon="arrow-path">
                Reset
            </flux:button>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-2 items-center border-t border-b border-gray-200 dark:border-gray-700 py-4">
            <flux:button wire:click="selectAllThisPage" variant="ghost" size="sm" icon="check">
                Select All This Page
            </flux:button>

            <flux:button wire:click="deselectAll" variant="ghost" size="sm" icon="x-mark">
                Deselect All
            </flux:button>

            <flux:spacer />

            @can('approve item price approval')
            <flux:button
                wire:click="confirmApprove"
                variant="primary"
                size="sm"
                icon="check-circle"
                :disabled="empty($selectedIds)"
            >
                Approve Selected ({{ count($selectedIds) }})
            </flux:button>
            @endcan

            @can('reject item price approval')
            <flux:button
                wire:click="confirmReject"
                variant="danger"
                size="sm"
                icon="x-circle"
                :disabled="empty($selectedIds)"
            >
                Reject Selected ({{ count($selectedIds) }})
            </flux:button>
            @endcan
        </div>

        {{-- Table --}}
        <flux:table :paginate="$this->pendings">
            <flux:table.columns>
                <flux:table.column class="w-12"></flux:table.column>
                <flux:table.column>Item Code</flux:table.column>
                <flux:table.column>Item Name</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column align="end">Old Price</flux:table.column>
                <flux:table.column align="end">New Price</flux:table.column>
                <flux:table.column align="end">Change</flux:table.column>
                <flux:table.column>Changed By</flux:table.column>
                <flux:table.column>Submitted At</flux:table.column>
                <flux:table.column>Status</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->pendings as $pending)
                    <flux:table.row :key="$pending->id">
                        <flux:table.cell>
                            @if($pending->status === 'pending')
                                <flux:checkbox
                                    wire:model.live="selectedIds"
                                    value="{{ $pending->id }}"
                                />
                            @endif
                        </flux:table.cell>
                        <flux:table.cell variant="strong">
                            {{ $pending->item?->code ?? 'N/A' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $pending->item?->name ?? 'N/A' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $pending->categoryPrice?->name ?? 'N/A' }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ number_format($pending->old_price, 2) }}
                        </flux:table.cell>
                        <flux:table.cell align="end" variant="strong">
                            {{ number_format($pending->new_price, 2) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ $pending->change_percentage_formatted }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $pending->submittedBy?->name ?? 'N/A' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $pending->submitted_at?->format('Y-m-d H:i') ?? 'N/A' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="match($pending->status) {
                                    'pending' => 'amber',
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'zinc'
                                }"
                            >
                                {{ ucfirst($pending->status) }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10" class="text-center text-gray-500 dark:text-gray-400 py-8">
                            No pending approvals found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Approve Confirmation Modal --}}
    <flux:modal name="approve-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Approve Selected Items</flux:heading>
                <flux:subheading class="mt-2">
                    You are about to approve {{ count($selectedIds) }} item(s).
                </flux:subheading>
            </div>

            <flux:textarea
                wire:model="approvalNotes"
                label="Notes (Optional)"
                placeholder="Add any notes for this approval..."
                rows="3"
            />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('approve-confirmation').close()">
                    Cancel
                </flux:button>
                <flux:button variant="primary" wire:click="processApproval" icon="check-circle">
                    Approve
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Reject Confirmation Modal --}}
    <flux:modal name="reject-confirmation" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Reject Selected Items</flux:heading>
                <flux:subheading class="mt-2">
                    You are about to reject {{ count($selectedIds) }} item(s).
                </flux:subheading>
            </div>

            <flux:textarea
                wire:model="approvalNotes"
                label="Notes (Optional)"
                placeholder="Add a reason for rejection..."
                rows="3"
            />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" x-on:click="$flux.modal('reject-confirmation').close()">
                    Cancel
                </flux:button>
                <flux:button variant="danger" wire:click="processRejection" icon="x-circle">
                    Reject
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
