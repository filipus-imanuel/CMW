<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Support\Facades\Auth;

class TransactionHelper
{
    /**
     * Recalculate and update order header totals from its details.
     * Uses lockForUpdate() to prevent concurrent modification.
     */
    public static function updateOrderTotals(OrderHeader $order): void
    {
        $order = $order->lockForUpdate()->fresh();

        $details = $order->details()->get();

        $subtotal = $details->sum('total');
        $totalDiscount = $details->sum('discount');
        $totalTax = $details->sum('tax');

        $order->update([
            'subtotal' => $subtotal,
            'discount' => $totalDiscount,
            'tax' => $totalTax,
            'total' => $subtotal,
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Recalculate a single order detail total.
     * Formula: total = (quantity * price) - discount + tax
     */
    public static function recalculateDetail(OrderDetail $detail): void
    {
        $subtotal = (float) $detail->quantity * (float) $detail->price;
        $total = $subtotal - (float) $detail->discount + (float) $detail->tax;

        $detail->update([
            'total' => $total,
            'updated_by' => Auth::id(),
        ]);
    }
}
