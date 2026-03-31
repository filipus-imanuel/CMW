<?php

namespace App\Livewire\Sales\Return;

use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\ReturnHeader;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sales Return Detail')]
class Show extends Component
{
    public $returnHeader = null;

    public $rejection_reason = '';

    public $allocations = [];

    public function mount($id): void
    {
        $this->authorize('view sales return');

        $this->returnHeader = ReturnHeader::with([
            'partner', 'orderHeader.company', 'orderHeader.currency',
            'deliveryHeader', 'arInvoice',
            'details.item', 'details.itemUom.uom', 'details.deliveryDetail',
            'approvedByUser', 'receivedByUser',
        ])->findOrFail($id);

        // Load allocations for PROCESSING status (post-warehouse, allocation type)
        if ($this->returnHeader->isProcessing() && $this->returnHeader->isWarehouseReceived() && $this->returnHeader->isAllocationType()) {
            $this->loadAllocations();
        }
    }

    protected function loadAllocations(): void
    {
        $this->allocations = $this->returnHeader->details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'item_code' => $detail->item?->code ?? '',
                'item_name' => $detail->item?->name ?? '',
                'uom_name' => $detail->itemUom?->uom?->name ?? '',
                'quantity_return' => (float) $detail->quantity_return,
                'quantity_received_good' => (float) $detail->quantity_received_good,
                'quantity_received_damaged' => (float) $detail->quantity_received_damaged,
                'quantity_redelivery' => (float) $detail->quantity_redelivery,
                'quantity_next_so' => (float) $detail->quantity_next_so,
            ];
        })->toArray();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // APPROVAL ACTIONS
    // ══════════════════════════════════════════════════════════════════════════

    public function processApproval(): void
    {
        $this->authorize('approve sales return');

        if (! $this->returnHeader->isApproval()) {
            Flux::toast('Return is not pending approval.', variant: 'danger', position: 'top-end');

            return;
        }

        try {
            DB::transaction(function () {
                // INVOICE_DISCARD: reduce AR balance and finish immediately (no warehouse step)
                if ($this->returnHeader->isInvoiceDiscard()) {
                    $this->returnHeader->update([
                        'status' => ReturnHeader::STATUS_FINISH,
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'updated_by' => Auth::id(),
                    ]);

                    $this->processInvoiceReduction();

                    $this->dispatch('shp.sales.return.finished', returnId: $this->returnHeader->id);

                    return;
                }

                // ITEM_INVOICE: reduce AR balance at approval, then proceed to warehouse
                if ($this->returnHeader->isItemInvoice()) {
                    $this->returnHeader->update([
                        'status' => ReturnHeader::STATUS_PROCESSING,
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'updated_by' => Auth::id(),
                    ]);

                    $this->processInvoiceReduction();

                    $this->dispatch('shp.sales.return.approved', returnId: $this->returnHeader->id);

                    return;
                }

                // ITEM & INVOICE_RETURN: proceed to warehouse
                $this->returnHeader->update([
                    'status' => ReturnHeader::STATUS_PROCESSING,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

                $this->dispatch('shp.sales.return.approved', returnId: $this->returnHeader->id);
            });

            Flux::toast("Return {$this->returnHeader->code} approved.", variant: 'success', position: 'top-end');
            $this->redirectRoute(
                $this->returnHeader->isFinish() ? 'sales.return.index.finish' : 'sales.return.index.approval',
                navigate: true
            );
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function processReject(): void
    {
        $this->authorize('reject sales return');

        if (! $this->returnHeader->isApproval()) {
            Flux::toast('Return is not pending approval.', variant: 'danger', position: 'top-end');

            return;
        }

        $this->validate([
            'rejection_reason' => 'required|string|max:1024',
        ]);

        try {
            DB::transaction(function () {
                $this->returnHeader->update([
                    'status' => ReturnHeader::STATUS_REJECTED,
                    'rejection_reason' => $this->rejection_reason,
                    'updated_by' => Auth::id(),
                ]);

                $this->dispatch('shp.sales.return.refresh.rejected', returnId: $this->returnHeader->id);
            });

            Flux::toast("Return {$this->returnHeader->code} rejected.", variant: 'success', position: 'top-end');
            $this->redirectRoute('sales.return.index.rejected', navigate: true);
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CANCEL (PROCESSING, pre-warehouse)
    // ══════════════════════════════════════════════════════════════════════════

    public function cancelReturn(): void
    {
        $this->authorize('edit sales return');

        if (! $this->returnHeader->isProcessing() || $this->returnHeader->isWarehouseReceived()) {
            Flux::toast('Cannot cancel — warehouse has already received the goods.', variant: 'danger', position: 'top-end');

            return;
        }

        try {
            DB::transaction(function () {
                $this->returnHeader->update([
                    'status' => ReturnHeader::STATUS_CANCELLED,
                    'updated_by' => Auth::id(),
                ]);

                $this->dispatch('shp.sales.return.cancelled', returnId: $this->returnHeader->id);
            });

            Flux::toast("Return {$this->returnHeader->code} cancelled.", variant: 'success', position: 'top-end');
            $this->redirectRoute('sales.return.index.ongoing', navigate: true);
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ALLOCATION ACTIONS (for ITEM type, post-warehouse receipt)
    // ══════════════════════════════════════════════════════════════════════════

    public function processAllocation(): void
    {
        $this->authorize('edit sales return');

        if (! $this->returnHeader->isProcessing() || ! $this->returnHeader->isWarehouseReceived()) {
            Flux::toast('Cannot allocate at this stage.', variant: 'danger', position: 'top-end');

            return;
        }

        // Validate: redelivery + next_so cannot exceed total returned qty
        // Both redelivery and next_so ultimately draw from warehouse stock during delivery
        foreach ($this->allocations as $index => $alloc) {
            $redeliveryQty = (float) $alloc['quantity_redelivery'];
            $nextSoQty = (float) $alloc['quantity_next_so'];
            $totalAllocated = $redeliveryQty + $nextSoQty;
            $maxQty = (float) $alloc['quantity_return'];

            if ($totalAllocated > $maxQty) {
                Flux::toast("Line #{$index}: Redelivery + Next SO ({$totalAllocated}) exceeds return qty ({$maxQty}).", variant: 'danger', position: 'top-end');

                return;
            }

            if ($redeliveryQty < 0 || $nextSoQty < 0) {
                Flux::toast('Quantities cannot be negative.', variant: 'danger', position: 'top-end');

                return;
            }
        }

        // Validate: warehouse has enough good stock for each redelivery line
        foreach ($this->allocations as $index => $alloc) {
            $redeliveryQty = (float) $alloc['quantity_redelivery'];
            if ($redeliveryQty <= 0) {
                continue;
            }

            $detail = $this->returnHeader->details()->with('deliveryDetail')->find($alloc['id']);
            if (! $detail || ! $detail->deliveryDetail) {
                Flux::toast("Line #{$index}: Cannot determine warehouse for stock check.", variant: 'danger', position: 'top-end');

                return;
            }

            $warehouseId = $detail->deliveryDetail->warehouse_id;
            $itemId = $detail->item_id;
            $itemUomId = $detail->item_uom_id;

            $baseRedeliveryQty = TransactionHelper::convertToBaseUom($redeliveryQty, $itemUomId);
            $baseBalance = TransactionHelper::getWarehouseBalance($itemId, $warehouseId);

            if ($baseBalance < $baseRedeliveryQty) {
                $displayBalance = TransactionHelper::getAvailableStock($itemId, $warehouseId, $itemUomId);
                Flux::toast(
                    "Line #{$index} ({$alloc['item_code']}): Insufficient stock for redelivery. Available: {$displayBalance}, Requested: {$redeliveryQty}.",
                    variant: 'danger',
                    position: 'top-end'
                );

                return;
            }
        }

        try {
            DB::transaction(function () {
                $hasRedelivery = false;

                foreach ($this->allocations as $alloc) {
                    $detail = $this->returnHeader->details()->find($alloc['id']);
                    if ($detail) {
                        $detail->update([
                            'quantity_redelivery' => (float) $alloc['quantity_redelivery'],
                            'quantity_next_so' => (float) $alloc['quantity_next_so'],
                            'updated_by' => Auth::id(),
                        ]);
                    }

                    if ((float) $alloc['quantity_redelivery'] > 0) {
                        $hasRedelivery = true;
                    }
                }

                // Set return to FINISH
                $this->returnHeader->update([
                    'status' => ReturnHeader::STATUS_FINISH,
                    'updated_by' => Auth::id(),
                ]);

                // If there's redelivery qty, revert SO status so new DO can be created
                if ($hasRedelivery) {
                    $order = $this->returnHeader->orderHeader;
                    if ($order && $order->status === 'FINISH') {
                        $order->update([
                            'status' => 'DELIVERY',
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                $this->dispatch('shp.sales.return.finished', returnId: $this->returnHeader->id);
            });

            Flux::toast("Return {$this->returnHeader->code} allocation completed.", variant: 'success', position: 'top-end');
            $this->redirectRoute('sales.return.index.ongoing', navigate: true);
        } catch (\Exception $e) {
            Flux::toast('Error: '.$e->getMessage(), variant: 'danger', position: 'top-end');
        }
    }

    public function render()
    {
        return view('livewire.sales.return.show');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INVOICE REDUCTION (shared by INVOICE_DISCARD approval)
    // ══════════════════════════════════════════════════════════════════════════

    protected function processInvoiceReduction(): void
    {
        $arInvoice = $this->returnHeader->arInvoice;
        if (! $arInvoice) {
            return;
        }

        $returnTotal = (float) $this->returnHeader->total;

        $newReturnTotal = (float) $arInvoice->return_total + $returnTotal;
        $newBalance = max(0, (float) $arInvoice->total - (float) $arInvoice->paid - $newReturnTotal);

        $newStatus = $newBalance <= 0
            ? ArInvoiceHeader::STATUS_PAID
            : ArInvoiceHeader::STATUS_PARTIAL;

        $arInvoice->update([
            'return_total' => round($newReturnTotal, 2),
            'balance' => round($newBalance, 2),
            'status' => $newStatus,
            'updated_by' => Auth::id(),
        ]);
    }
}
