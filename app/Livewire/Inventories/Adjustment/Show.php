<?php

namespace App\Livewire\Inventories\Adjustment;

use App\Helpers\CMW\TransactionHelper;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Transaction\StockAdjustmentHeader;
use Exception;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Stock Adjustment Detail')]
class Show extends Component
{
    public ?StockAdjustmentHeader $header = null;

    public function mount($id): void
    {
        $this->authorize('view stock adjustment');

        $this->header = StockAdjustmentHeader::with([
            'warehouse', 'details.item', 'details.itemUom.uom',
            'createdBy', 'updatedBy', 'inventoryLedgers',
        ])->findOrFail($id);
    }

    /**
     * Confirm the stock adjustment — creates InventoryLedger entries.
     */
    public function confirm(): void
    {
        $this->authorize('confirm stock adjustment');

        if (! $this->header->isDraft()) {
            Flux::toast('Only draft adjustments can be confirmed.', variant: 'danger', position: 'top right');

            return;
        }

        try {
            DB::transaction(function () {
                $this->header->lockForUpdate();

                foreach ($this->header->details as $detail) {
                    if ((float) $detail->quantity_difference == 0) {
                        continue;
                    }

                    $difference = (float) $detail->quantity_difference;

                    // Convert difference from selected UOM to base UOM
                    $baseDifference = TransactionHelper::convertToBaseUom($difference, $detail->item_uom_id);
                    $isPositive = $baseDifference > 0;

                    // Get current balance from inventory ledger (always in base UOM)
                    $currentBalance = InventoryLedger::where('item_id', $detail->item_id)
                        ->where('warehouse_id', $this->header->warehouse_id)
                        ->orderByDesc('date')
                        ->orderByDesc('id')
                        ->value('balance') ?? 0;

                    $newBalance = (float) $currentBalance + $baseDifference;

                    // Get unit cost from item
                    $unitCost = Item::where('id', $detail->item_id)->value('cost_price') ?? 0;

                    InventoryLedger::create([
                        'item_id' => $detail->item_id,
                        'warehouse_id' => $this->header->warehouse_id,
                        'date' => $this->header->date,
                        'type' => 'adjustment',
                        'reference_type' => StockAdjustmentHeader::class,
                        'reference_id' => $this->header->id,
                        'quantity_in' => $isPositive ? abs($baseDifference) : 0,
                        'quantity_out' => $isPositive ? 0 : abs($baseDifference),
                        'balance' => $newBalance,
                        'unit_cost' => $unitCost,
                        'remarks' => 'Stock Adjustment: '.$this->header->code.($detail->remarks ? ' - '.$detail->remarks : ''),
                        'created_by' => Auth::id(),
                    ]);
                }

                $this->header->update([
                    'status' => StockAdjustmentHeader::STATUS_CONFIRMED,
                    'is_edit_locked' => true,
                    'is_delete_locked' => true,
                    'updated_by' => Auth::id(),
                ]);

                // Reload the model
                $this->header->refresh();
                $this->header->load([
                    'warehouse', 'details.item', 'details.itemUom.uom',
                    'createdBy', 'updatedBy', 'inventoryLedgers',
                ]);

                Flux::toast('Stock adjustment confirmed successfully. Inventory ledger entries created.', variant: 'success', position: 'top right');
                $this->dispatch('shp.inventories.stock-adjustment.confirmed');
            });
        } catch (Exception $e) {
            Flux::toast('An error occurred while confirming the stock adjustment.', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    /**
     * Cancel the stock adjustment.
     * If draft: just mark as cancelled.
     * If confirmed: create reversal InventoryLedger entries.
     */
    public function cancel(): void
    {
        $this->authorize('cancel stock adjustment');

        if ($this->header->isCancelled()) {
            Flux::toast('This adjustment is already cancelled.', variant: 'danger', position: 'top right');

            return;
        }

        try {
            DB::transaction(function () {
                $this->header->lockForUpdate();

                if ($this->header->isConfirmed()) {
                    // Create reversal ledger entries
                    foreach ($this->header->details as $detail) {
                        if ((float) $detail->quantity_difference == 0) {
                            continue;
                        }

                        $difference = (float) $detail->quantity_difference;

                        // Convert difference from selected UOM to base UOM
                        $baseDifference = TransactionHelper::convertToBaseUom($difference, $detail->item_uom_id);
                        $isPositive = $baseDifference > 0;

                        // Get current balance (always in base UOM)
                        $currentBalance = InventoryLedger::where('item_id', $detail->item_id)
                            ->where('warehouse_id', $this->header->warehouse_id)
                            ->orderByDesc('date')
                            ->orderByDesc('id')
                            ->value('balance') ?? 0;

                        // Reverse: subtract what was added, add what was subtracted
                        $newBalance = (float) $currentBalance - $baseDifference;

                        $unitCost = Item::where('id', $detail->item_id)->value('cost_price') ?? 0;

                        InventoryLedger::create([
                            'item_id' => $detail->item_id,
                            'warehouse_id' => $this->header->warehouse_id,
                            'date' => now()->toDateString(),
                            'type' => 'adjustment',
                            'reference_type' => StockAdjustmentHeader::class,
                            'reference_id' => $this->header->id,
                            'quantity_in' => $isPositive ? 0 : abs($baseDifference),
                            'quantity_out' => $isPositive ? abs($baseDifference) : 0,
                            'balance' => $newBalance,
                            'unit_cost' => $unitCost,
                            'remarks' => 'REVERSAL - Stock Adjustment: '.$this->header->code,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }

                $this->header->update([
                    'status' => StockAdjustmentHeader::STATUS_CANCELLED,
                    'is_edit_locked' => true,
                    'is_delete_locked' => true,
                    'updated_by' => Auth::id(),
                ]);

                // Reload the model
                $this->header->refresh();
                $this->header->load([
                    'warehouse', 'details.item', 'details.itemUom.uom',
                    'createdBy', 'updatedBy', 'inventoryLedgers',
                ]);

                Flux::toast('Stock adjustment cancelled successfully.', variant: 'success', position: 'top right');
                $this->dispatch('shp.inventories.stock-adjustment.cancelled');
            });
        } catch (Exception $e) {
            Flux::toast('An error occurred while cancelling the stock adjustment.', variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.inventories.adjustment.show');
    }
}
