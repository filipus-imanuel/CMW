<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\StockAdjustmentHeader;

class CodeGeneratorHelper
{
    /**
     * Generate order code based on status context.
     *
     * Format: SR/YYMM/0001 (Sales Request) or SO/YYMM/0001 (Sales Order)
     *
     * @param  string  $prefix  The prefix to use (SR, SO, PO, etc.)
     * @return string The generated code
     */
    public static function generateOrderCode(string $prefix = 'SR'): string
    {
        $yearMonth = date('ym');

        // Determine which column to search based on prefix
        $column = $prefix === 'SO' ? 'code_order' : 'code_request';

        $last = OrderHeader::withTrashed()
            ->where($column, 'like', "{$prefix}/{$yearMonth}/%")
            ->orderByDesc($column)
            ->first();

        $number = $last ? (int) substr($last->{$column}, -4) + 1 : 1;

        return "{$prefix}/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate stock adjustment code.
     *
     * Format: SA/YYMM/0001
     *
     * @return string The generated code
     */
    public static function generateAdjustmentCode(): string
    {
        $yearMonth = date('ym');

        $last = StockAdjustmentHeader::withTrashed()
            ->where('code', 'like', "SA/{$yearMonth}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -4) + 1 : 1;

        return "SA/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate delivery order code.
     *
     * Format: DO/YYMM/0001
     *
     * @return string The generated code
     */
    public static function generateDeliveryCode(): string
    {
        $yearMonth = date('ym');

        $last = DeliveryHeader::withTrashed()
            ->where('code', 'like', "DO/{$yearMonth}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -4) + 1 : 1;

        return "DO/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate AR invoice code.
     *
     * Format: INV/YYMM/0001
     *
     * @return string The generated code
     */
    public static function generateInvoiceCode(): string
    {
        $yearMonth = date('ym');

        $last = \App\Models\CMW\Transaction\ArInvoiceHeader::withTrashed()
            ->where('code', 'like', "INV/{$yearMonth}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -4) + 1 : 1;

        return "INV/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate return code.
     *
     * Format: RTN/YYMM/0001
     *
     * @return string The generated code
     */
    public static function generateReturnCode(): string
    {
        $yearMonth = date('ym');

        $last = \App\Models\CMW\Transaction\ReturnHeader::withTrashed()
            ->where('code', 'like', "RTN/{$yearMonth}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -4) + 1 : 1;

        return "RTN/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
