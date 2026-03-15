<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Support\Facades\Auth;

class TransactionHelper
{
    /**
     * Calculate tax amount for a single item based on tax mode.
     *
     * @param  float  $quantity  Item quantity
     * @param  float  $price  Item price (inclusive if INCLUDE mode)
     * @param  float  $discount  Item discount amount
     * @param  string  $taxMode  INCLUDE, EXCLUDE, or NONE
     * @param  float  $taxRate  Tax rate percentage (e.g. 11.00 for 11%)
     * @return array{tax: float, subtotal: float, total: float}
     */
    public static function calculateItemTax(
        float $quantity,
        float $price,
        float $discount,
        string $taxMode,
        float $taxRate
    ): array {
        $lineSubtotal = ($quantity * $price) - $discount;

        if ($taxMode === 'INCLUDE' && $taxRate > 0) {
            // Tax inclusive: price already includes tax
            // Base amount = lineSubtotal / (1 + rate/100)
            $rateMultiplier = 1 + ($taxRate / 100);
            $baseAmount = $lineSubtotal / $rateMultiplier;
            $taxAmount = $lineSubtotal - $baseAmount;

            return [
                'tax' => round($taxAmount, 2),
                'subtotal' => round($baseAmount, 2),
                'total' => round($lineSubtotal, 2), // Price already includes tax
            ];
        }

        if ($taxMode === 'EXCLUDE' && $taxRate > 0) {
            // Tax exclusive: tax added on top
            $taxAmount = $lineSubtotal * ($taxRate / 100);
            $total = $lineSubtotal + $taxAmount;

            return [
                'tax' => round($taxAmount, 2),
                'subtotal' => round($lineSubtotal, 2),
                'total' => round($total, 2),
            ];
        }

        // NONE or zero rate
        return [
            'tax' => 0.00,
            'subtotal' => round($lineSubtotal, 2),
            'total' => round($lineSubtotal, 2),
        ];
    }

    /**
     * Recalculate and update order header totals from its details.
     * Uses lockForUpdate() to prevent concurrent modification.
     *
     * Subtotal = sum of (qty * price - discount) for all items
     * Tax = sum of calculated tax for all items
     * Total = subtotal + tax (for EXCLUDE/NONE) or subtotal (for INCLUDE, since price includes tax)
     */
    public static function updateOrderTotals(OrderHeader $order): void
    {
        // Lock and refresh the order
        $order = OrderHeader::lockForUpdate()->find($order->id);

        $details = $order->details()->get();

        $taxMode = $order->tax_mode ?? 'NONE';
        $taxRate = (float) ($order->tax_rate ?? 0);

        $totalSubtotal = 0;
        $totalDiscount = $details->sum(fn ($d) => (float) $d->discount);
        $totalTax = 0;
        $grandTotal = 0;

        foreach ($details as $detail) {
            $calc = self::calculateItemTax(
                (float) $detail->quantity,
                (float) $detail->price_proposed,
                (float) $detail->discount,
                $taxMode,
                $taxRate
            );

            $totalSubtotal += $calc['subtotal'];
            $totalTax += $calc['tax'];
            $grandTotal += $calc['total'];
        }

        $order->update([
            'subtotal' => $totalSubtotal,
            'discount' => $totalDiscount,
            'tax' => $totalTax,
            'total' => $grandTotal,
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Recalculate a single order detail's tax and total based on header tax settings.
     */
    public static function recalculateDetail(OrderDetail $detail): void
    {
        $header = $detail->header;
        $taxMode = $header->tax_mode ?? 'NONE';
        $taxRate = (float) ($header->tax_rate ?? 0);

        $calc = self::calculateItemTax(
            (float) $detail->quantity,
            (float) $detail->price_proposed,
            (float) $detail->discount,
            $taxMode,
            $taxRate
        );

        $detail->update([
            'tax' => $calc['tax'],
            'total' => $calc['total'],
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Get the current warehouse balance for an item in base UOM.
     */
    public static function getWarehouseBalance(int $itemId, int $warehouseId): float
    {
        return (float) (InventoryLedger::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance') ?? 0);
    }

    /**
     * Get the available stock for an item in a warehouse, converted to the given UOM.
     * Returns the balance in the line's UOM (or base UOM when itemUomId is null).
     */
    public static function getAvailableStock(int $itemId, int $warehouseId, ?int $itemUomId = null): float
    {
        $baseBalance = self::getWarehouseBalance($itemId, $warehouseId);

        if ($itemUomId) {
            $conversionRate = (float) (ItemUom::where('id', $itemUomId)->value('conversion_rate') ?? 1);
            if ($conversionRate > 0) {
                return round($baseBalance / $conversionRate, 2);
            }
        }

        return round($baseBalance, 2);
    }

    /**
     * Convert a quantity to base UOM using the given item UOM.
     */
    public static function convertToBaseUom(float $quantity, ?int $itemUomId): float
    {
        if ($itemUomId) {
            $conversionRate = (float) (ItemUom::where('id', $itemUomId)->value('conversion_rate') ?? 1);

            return $quantity * max($conversionRate, 1);
        }

        return $quantity;
    }

    /**
     * Recalculate all details for an order based on its tax mode/rate.
     */
    public static function recalculateAllDetails(OrderHeader $order): void
    {
        $taxMode = $order->tax_mode ?? 'NONE';
        $taxRate = (float) ($order->tax_rate ?? 0);

        foreach ($order->details as $detail) {
            $calc = self::calculateItemTax(
                (float) $detail->quantity,
                (float) $detail->price_proposed,
                (float) $detail->discount,
                $taxMode,
                $taxRate
            );

            $detail->update([
                'tax' => $calc['tax'],
                'total' => $calc['total'],
                'updated_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Check if all AR invoices for a given SO are paid, and if so mark the SO as FINAL.
     * If any invoice becomes unpaid again (e.g. payment cancelled), revert FINAL to FINISH.
     */
    public static function checkAndUpdateOrderFinalStatus(int $orderHeaderId): void
    {
        $order = OrderHeader::lockForUpdate()->find($orderHeaderId);

        if (! $order || ! in_array($order->status, ['FINISH', 'FINAL'])) {
            return;
        }

        $invoices = ArInvoiceHeader::where('order_header_id', $orderHeaderId)->get();

        if ($invoices->isEmpty()) {
            return;
        }

        $allPaid = $invoices->every(fn ($inv) => $inv->status === ArInvoiceHeader::STATUS_PAID);

        if ($allPaid && $order->status === 'FINISH') {
            $order->update([
                'status' => 'FINAL',
                'updated_by' => Auth::id(),
            ]);
        } elseif (! $allPaid && $order->status === 'FINAL') {
            $order->update([
                'status' => 'FINISH',
                'updated_by' => Auth::id(),
            ]);
        }
    }
}
