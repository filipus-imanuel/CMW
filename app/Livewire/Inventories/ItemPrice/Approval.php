<?php

namespace App\Livewire\Inventories\ItemPrice;

use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\PendingItemPrice;
use Exception;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Item Price Approvals')]
#[Layout('components.layouts.app')]
class Approval extends Component
{
    use WithPagination;

    // Session-persisted filters
    #[Session]
    public string $statusFilter = 'pending';

    #[Session]
    public ?int $categoryFilter = null;

    #[Session]
    public ?string $dateFrom = null;

    #[Session]
    public ?string $dateTo = null;

    // Selection & approval
    public array $selectedIds = [];

    public string $approvalNotes = '';

    /**
     * Get pending item prices with filters applied.
     */
    #[Computed]
    public function pendings()
    {
        return PendingItemPrice::query()
            ->with(['itemUom.item', 'itemUom.uom', 'categoryPrice', 'submittedBy', 'approvedBy'])
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_price_id', $this->categoryFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('submitted_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('submitted_at', '<=', $this->dateTo))
            ->latest('submitted_at')
            ->paginate(100);
    }

    /**
     * Get all category prices for filter dropdown.
     */
    #[Computed]
    public function categories()
    {
        return CategoryPrice::active()->orderBy('name')->get();
    }

    public function mount(): void
    {
        $this->authorize('view item price approval');
    }

    /**
     * Select all items on current page.
     */
    public function selectAllThisPage(): void
    {
        $this->selectedIds = $this->pendings->pluck('id')->toArray();
    }

    /**
     * Deselect all items.
     */
    public function deselectAll(): void
    {
        $this->selectedIds = [];
    }

    /**
     * Reset all filters to defaults.
     */
    public function resetFilters(): void
    {
        $this->statusFilter = 'pending';
        $this->categoryFilter = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->selectedIds = [];
        $this->resetPage();
    }

    /**
     * Show approval confirmation modal.
     */
    public function confirmApprove(): void
    {
        if (empty($this->selectedIds)) {
            Flux::toast('Please select at least one item', variant: 'warning', position: 'top right');

            return;
        }

        $this->modal('approve-confirmation')->show();
    }

    /**
     * Show rejection confirmation modal.
     */
    public function confirmReject(): void
    {
        if (empty($this->selectedIds)) {
            Flux::toast('Please select at least one item', variant: 'warning', position: 'top right');

            return;
        }

        $this->modal('reject-confirmation')->show();
    }

    /**
     * Process approval for selected items with separate transactions.
     */
    public function processApproval(): void
    {
        try {
            $this->authorize('approve item price approval');
        } catch (\Exception $e) {
            Flux::toast('You do not have permission to approve', variant: 'danger', position: 'top right');
            return;
        }

        $successCount = 0;
        $failedCount = 0;
        $skippedSelfApproval = 0;

        foreach ($this->selectedIds as $id) {
            try {
                DB::transaction(function () use ($id, &$successCount, &$skippedSelfApproval) {
                    // Lock and fetch the pending record
                    $pending = PendingItemPrice::where('id', $id)
                        ->where('status', 'pending')
                        ->lockForUpdate()
                        ->first();

                    if (! $pending) {
                        // Already processed by another user
                        return;
                    }

                    // Prevent self-approval (bypass for Super Admin)
                    $user = Auth::user();
                    if ((int) $pending->submitted_by === $user->id && ! $user->hasRole('Super Admin')) {
                        $skippedSelfApproval++;

                        return;
                    }

                    // Update pending record to approved
                    $pending->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'processed_at' => now(),
                        'approval_notes' => $this->approvalNotes ?: null,
                        'updated_by' => Auth::id(),
                    ]);

                    // Update the actual item price
                    $itemPrice = ItemPrice::lockForUpdate()->find($pending->item_price_id);
                    if ($itemPrice) {
                        $itemPrice->update([
                            'price' => $pending->new_price,
                            'updated_by' => Auth::id(),
                        ]);

                        // Create history record
                        HistoryItemPrice::create([
                            'item_uom_id' => $pending->item_uom_id,
                            'category_price_id' => $pending->category_price_id,
                            'old_price' => $pending->old_price,
                            'new_price' => $pending->new_price,
                            'created_by' => Auth::id(),
                        ]);
                    }

                    $successCount++;
                });
            } catch (QueryException $e) {
                Log::error('Error approving item price: '.$e->getMessage());
                $failedCount++;
            } catch (Exception $e) {
                Log::error('Error approving item price: '.$e->getMessage());
                $failedCount++;
            }
        }

        // Show appropriate toast messages
        $messages = [];
        if ($successCount > 0) {
            $messages[] = "Approved: {$successCount}";
        }
        if ($failedCount > 0) {
            $messages[] = "Failed: {$failedCount}";
        }
        if ($skippedSelfApproval > 0) {
            $messages[] = "Skipped (self-approval not allowed): {$skippedSelfApproval}";
        }

        if (empty($messages)) {
            Flux::toast('No items were processed', variant: 'warning', position: 'top right');
        } else {
            $variant = $failedCount > 0 || $skippedSelfApproval > 0 ? 'warning' : 'success';
            Flux::toast(implode(', ', $messages), variant: $variant, position: 'top right');
        }

        // Close modal and dispatch events
        $this->modal('approve-confirmation')->close();
        $this->dispatch('item-price-approval.badge-refresh');
        $this->dispatch('item-price-approval-refresh')->to('components.badges.item-price-pending-approval');
        $this->js('window.dispatchEvent(new CustomEvent("item-price-approval-refresh"))');

        // Reset state
        $this->selectedIds = [];
        $this->approvalNotes = '';
        $this->resetPage();
    }

    /**
     * Process rejection for selected items with separate transactions.
     */
    public function processRejection(): void
    {
        $this->authorize('reject item price approval');

        $successCount = 0;
        $failedCount = 0;

        foreach ($this->selectedIds as $id) {
            try {
                DB::transaction(function () use ($id, &$successCount) {
                    // Lock and fetch the pending record
                    $pending = PendingItemPrice::where('id', $id)
                        ->where('status', 'pending')
                        ->lockForUpdate()
                        ->first();

                    if (! $pending) {
                        // Already processed by another user
                        return;
                    }

                    // Update pending record to rejected
                    $pending->update([
                        'status' => 'rejected',
                        'approved_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'processed_at' => now(),
                        'approval_notes' => $this->approvalNotes ?: null,
                        'updated_by' => Auth::id(),
                    ]);

                    $successCount++;
                });
            } catch (QueryException $e) {
                Log::error('Error rejecting item price: '.$e->getMessage());
                $failedCount++;
            } catch (Exception $e) {
                Log::error('Error rejecting item price: '.$e->getMessage());
                $failedCount++;
            }
        }

        // Show toast message
        $message = "Rejected: {$successCount}";
        if ($failedCount > 0) {
            $message .= ", Failed: {$failedCount}";
        }

        $variant = $failedCount > 0 ? 'warning' : 'success';
        Flux::toast($message, variant: $variant, position: 'top right');

        // Close modal and dispatch events
        $this->modal('reject-confirmation')->close();
        $this->dispatch('item-price-approval.badge-refresh');
        $this->dispatch('item-price-approval-refresh')->to('components.badges.item-price-pending-approval');
        $this->js('window.dispatchEvent(new CustomEvent("item-price-approval-refresh"))');

        // Reset state
        $this->selectedIds = [];
        $this->approvalNotes = '';
        $this->resetPage();
    }

    /**
     * Listen for refresh events.
     */
    #[On('item-price-approval.refresh')]
    public function refreshTable(): void
    {
        // This will trigger a re-render
    }

    public function render()
    {
        return view('livewire.inventories.item-price.approval');
    }
}
