<?php

namespace App\Livewire\Warehouses\Delivery\Ongoing;

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use Exception;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Delivery Order Detail')]
class Show extends Component
{
    public ?DeliveryHeader $header = null;

    public $receivedQuantities = [];

    public $cancelReason = '';

    public $resendNote = '';

    public function mount($id): void
    {
        $this->authorize('view delivery order');

        $this->header = DeliveryHeader::with([
            'orderHeader', 'partner', 'company', 'currency',
            'details.item', 'details.itemUom.uom', 'details.warehouse',
            'createdBy', 'confirmedByUser', 'arInvoices', 'inventoryLedgers',
        ])->findOrFail($id);

        // Initialize received quantities (default = quantity_sent)
        foreach ($this->header->details as $detail) {
            $this->receivedQuantities[$detail->id] = number_format((float) $detail->quantity_sent, 2, '.', '');
        }
    }

    /**
     * Confirm receipt of delivery — creates AR Invoice.
     */
    public function confirmReceipt(): void
    {
        $this->authorize('confirm delivery order');

        if (! $this->header->isOngoing()) {
            Flux::toast('Only ongoing deliveries can be confirmed.', variant: 'danger', position: 'top right');

            return;
        }

        // Validate received quantities
        foreach ($this->receivedQuantities as $detailId => $qty) {
            if (! is_numeric($qty) || (float) $qty < 0) {
                Flux::toast('Received quantity must be a non-negative number.', variant: 'danger', position: 'top right');

                return;
            }
        }

        try {
            DB::transaction(function () {
                $this->header->lockForUpdate();

                $taxMode = $this->header->orderHeader->tax_mode ?? 'NONE';
                $taxRate = (float) ($this->header->orderHeader->tax_rate ?? 0);

                $invoiceSubtotal = 0;
                $invoiceTax = 0;
                $invoiceTotal = 0;

                // Update received quantities on each detail
                foreach ($this->header->details as $detail) {
                    $qtyReceived = (float) ($this->receivedQuantities[$detail->id] ?? $detail->quantity_sent);
                    $detail->update([
                        'quantity_received' => $qtyReceived,
                        'updated_by' => Auth::id(),
                    ]);

                    // Recalculate line total based on received qty
                    $price = (float) $detail->price;
                    $discount = (float) $detail->discount;

                    // Proportional discount adjustment for received qty vs sent qty
                    $sentQty = (float) $detail->quantity_sent;
                    $proportionalDiscount = $sentQty > 0
                        ? round($discount * ($qtyReceived / $sentQty), 2)
                        : 0;

                    $calc = \App\Helpers\CMW\TransactionHelper::calculateItemTax(
                        $qtyReceived,
                        $price,
                        $proportionalDiscount,
                        $taxMode,
                        $taxRate
                    );

                    $invoiceSubtotal += $calc['subtotal'];
                    $invoiceTax += $calc['tax'];
                    $invoiceTotal += $calc['total'];
                }

                // Update delivery header
                $this->header->update([
                    'status' => DeliveryHeader::STATUS_FINISHED,
                    'confirmed_by' => Auth::id(),
                    'confirmed_at' => now(),
                    'is_edit_locked' => true,
                    'is_delete_locked' => true,
                    'updated_by' => Auth::id(),
                ]);

                // Create AR Invoice
                $this->createArInvoice($invoiceSubtotal, $invoiceTax, $invoiceTotal);

                // Check if SO is fully delivered
                $this->checkAndUpdateSoStatus();

                Flux::toast('Delivery confirmed and invoice created successfully.', variant: 'success', position: 'top right');
                $this->redirectRoute('warehouses.delivery.finish.show', ['id' => $this->header->id], navigate: true);
            });
        } catch (Exception $e) {
            Flux::toast('Error confirming delivery: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    /**
     * Force finish — marks delivery as finished regardless of remaining SO qty.
     * SO status is set to FINISH.
     */
    public function forceFinish(): void
    {
        $this->authorize('force finish delivery order');

        if (! $this->header->isOngoing()) {
            Flux::toast('Only ongoing deliveries can be force-finished.', variant: 'danger', position: 'top right');

            return;
        }

        try {
            DB::transaction(function () {
                $this->header->lockForUpdate();

                $taxMode = $this->header->orderHeader->tax_mode ?? 'NONE';
                $taxRate = (float) ($this->header->orderHeader->tax_rate ?? 0);

                $invoiceSubtotal = 0;
                $invoiceTax = 0;
                $invoiceTotal = 0;

                // Set received = sent for all details
                foreach ($this->header->details as $detail) {
                    $qtyReceived = (float) ($this->receivedQuantities[$detail->id] ?? $detail->quantity_sent);
                    $detail->update([
                        'quantity_received' => $qtyReceived,
                        'updated_by' => Auth::id(),
                    ]);

                    $price = (float) $detail->price;
                    $discount = (float) $detail->discount;
                    $sentQty = (float) $detail->quantity_sent;
                    $proportionalDiscount = $sentQty > 0
                        ? round($discount * ($qtyReceived / $sentQty), 2)
                        : 0;

                    $calc = \App\Helpers\CMW\TransactionHelper::calculateItemTax(
                        $qtyReceived,
                        $price,
                        $proportionalDiscount,
                        $taxMode,
                        $taxRate
                    );

                    $invoiceSubtotal += $calc['subtotal'];
                    $invoiceTax += $calc['tax'];
                    $invoiceTotal += $calc['total'];
                }

                // Update delivery header
                $this->header->update([
                    'status' => DeliveryHeader::STATUS_FINISHED,
                    'confirmed_by' => Auth::id(),
                    'confirmed_at' => now(),
                    'is_edit_locked' => true,
                    'is_delete_locked' => true,
                    'remarks' => trim(($this->header->remarks ?? '').' [Force Finished]'),
                    'updated_by' => Auth::id(),
                ]);

                // Create AR Invoice
                $this->createArInvoice($invoiceSubtotal, $invoiceTax, $invoiceTotal);

                // Force SO to FINISH regardless of remaining qty
                $order = OrderHeader::lockForUpdate()->find($this->header->order_header_id);
                if ($order && in_array($order->status, ['ORDER', 'DELIVERY'])) {
                    $order->update([
                        'status' => 'FINISH',
                        'updated_by' => Auth::id(),
                    ]);
                }

                Flux::toast('Delivery force-finished and invoice created. SO marked as FINISH.', variant: 'success', position: 'top right');
                $this->redirectRoute('warehouses.delivery.finish.show', ['id' => $this->header->id], navigate: true);
            });
        } catch (Exception $e) {
            Flux::toast('Error force-finishing delivery: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    /**
     * Cancel delivery with reason — reverse stock, update SO status if needed.
     */
    public function cancelDelivery(): void
    {
        $this->authorize('cancel delivery order');

        if (! $this->header->isOngoing()) {
            Flux::toast('Only ongoing deliveries can be cancelled.', variant: 'danger', position: 'top right');

            return;
        }

        if (empty(trim($this->cancelReason))) {
            Flux::toast('Cancel reason is required.', variant: 'danger', position: 'top right');

            return;
        }

        try {
            DB::transaction(function () {
                $this->header->lockForUpdate();

                // Reverse stock for each detail (return to inventory)
                foreach ($this->header->details as $detail) {
                    $itemUomId = $detail->item_uom_id;
                    $baseQty = (float) $detail->quantity_sent;
                    if ($itemUomId) {
                        $conversionRate = (float) (ItemUom::where('id', $itemUomId)->value('conversion_rate') ?? 1);
                        $baseQty = (float) $detail->quantity_sent * $conversionRate;
                    }

                    $currentBalance = (float) (InventoryLedger::where('item_id', $detail->item_id)
                        ->where('warehouse_id', $detail->warehouse_id)
                        ->orderByDesc('date')
                        ->orderByDesc('id')
                        ->value('balance') ?? 0);

                    $unitCost = (float) (Item::where('id', $detail->item_id)->value('cost_price') ?? 0);

                    InventoryLedger::create([
                        'item_id' => $detail->item_id,
                        'warehouse_id' => $detail->warehouse_id,
                        'date' => now()->toDateString(),
                        'type' => 'sales',
                        'reference_type' => DeliveryHeader::class,
                        'reference_id' => $this->header->id,
                        'quantity_in' => $baseQty,
                        'quantity_out' => 0,
                        'balance' => $currentBalance + $baseQty,
                        'unit_cost' => $unitCost,
                        'remarks' => 'REVERSAL - Delivery Order: '.$this->header->code,
                        'created_by' => Auth::id(),
                    ]);
                }

                // Update delivery header
                $this->header->update([
                    'status' => DeliveryHeader::STATUS_CANCELLED,
                    'cancel_reason' => $this->cancelReason,
                    'is_edit_locked' => true,
                    'is_delete_locked' => true,
                    'updated_by' => Auth::id(),
                ]);

                // Check if SO should revert to ORDER (no remaining ongoing/finished deliveries)
                $order = OrderHeader::lockForUpdate()->find($this->header->order_header_id);
                if ($order && $order->status === 'DELIVERY') {
                    $hasActiveDeliveries = DeliveryHeader::where('order_header_id', $order->id)
                        ->whereIn('status', ['ongoing', 'finished'])
                        ->exists();

                    if (! $hasActiveDeliveries) {
                        $order->update([
                            'status' => 'ORDER',
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                Flux::toast('Delivery cancelled and stock returned.', variant: 'success', position: 'top right');
                $this->redirectRoute('warehouses.delivery.cancelled.show', ['id' => $this->header->id], navigate: true);
            });
        } catch (Exception $e) {
            Flux::toast('Error cancelling delivery: '.$e->getMessage(), variant: 'danger', position: 'top right');
            throw $e;
        }
    }

    /**
     * Re-send request — just adds a note, no status change.
     */
    public function resendRequest(): void
    {
        $this->authorize('view delivery order');

        if (! $this->header->isOngoing()) {
            Flux::toast('Only ongoing deliveries can have re-send requests.', variant: 'danger', position: 'top right');

            return;
        }

        $note = trim($this->resendNote);
        $existingRemarks = $this->header->remarks ?? '';
        $timestamp = now()->format('d M Y H:i');
        $user = Auth::user()->name;
        $newRemark = "[Re-send Request by {$user} at {$timestamp}]".($note ? ": {$note}" : '');

        $this->header->update([
            'remarks' => trim($existingRemarks."\n".$newRemark),
            'updated_by' => Auth::id(),
        ]);

        $this->resendNote = '';
        $this->refreshHeader();

        Flux::toast('Re-send request noted.', variant: 'success', position: 'top right');
    }

    /**
     * Create AR Invoice from delivery.
     */
    private function createArInvoice(float $subtotal, float $tax, float $total): void
    {
        // Determine due date from partner credit term if available
        $dueDate = now()->addDays(30); // Default 30 days

        $partner = $this->header->partner;
        if ($partner) {
            $creditTerm = DB::table('credit_terms')
                ->join('partners', 'partners.id', '=', DB::raw($partner->id))
                ->first();
            // Fallback to 30 days if no credit term
        }

        ArInvoiceHeader::create([
            'code' => CodeGeneratorHelper::generateInvoiceCode(),
            'date' => now()->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'currency_id' => $this->header->currency_id,
            'partner_id' => $this->header->partner_id,
            'order_header_id' => $this->header->order_header_id,
            'delivery_header_id' => $this->header->id,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
            'paid' => 0,
            'balance' => round($total, 2),
            'status' => 'unpaid',
            'remarks' => 'Auto-generated from Delivery Order: '.$this->header->code,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Check if all SO quantities are delivered and update SO status.
     */
    private function checkAndUpdateSoStatus(): void
    {
        $order = OrderHeader::lockForUpdate()
            ->with('details')
            ->find($this->header->order_header_id);

        if (! $order || $order->status !== 'DELIVERY') {
            return;
        }

        $allDelivered = true;
        foreach ($order->details as $orderDetail) {
            $totalReceived = DB::table('delivery_details')
                ->join('delivery_headers', 'delivery_headers.id', '=', 'delivery_details.delivery_header_id')
                ->where('delivery_details.order_detail_id', $orderDetail->id)
                ->where('delivery_headers.status', 'finished')
                ->whereNull('delivery_headers.deleted_at')
                ->sum('delivery_details.quantity_received');

            if ((float) $totalReceived < (float) $orderDetail->quantity) {
                $allDelivered = false;
                break;
            }
        }

        if ($allDelivered) {
            $order->update([
                'status' => 'FINISH',
                'updated_by' => Auth::id(),
            ]);
        }
    }

    private function refreshHeader(): void
    {
        $this->header->refresh();
        $this->header->load([
            'orderHeader', 'partner', 'company', 'currency',
            'details.item', 'details.itemUom.uom', 'details.warehouse',
            'createdBy', 'confirmedByUser', 'arInvoices', 'inventoryLedgers',
        ]);
    }

    public function render()
    {
        return view('livewire.warehouses.delivery.ongoing.show');
    }
}
