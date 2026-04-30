<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Master\Company;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\ArPaymentHeader;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\ReturnHeader;
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
     * Generate work order auto code.
     *
     * Format: WO/YYMM/0001
     *
     * @return string The generated code
     */
    public static function generateWorkOrderCode(): string
    {
        $yearMonth = date('ym');

        $last = OrderHeader::withTrashed()
            ->where('work_order_auto', 'like', "WO/{$yearMonth}/%")
            ->orderByDesc('work_order_auto')
            ->first();

        $number = $last ? (int) substr($last->work_order_auto, -4) + 1 : 1;

        return "WO/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
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

        $last = ArInvoiceHeader::withTrashed()
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

        $last = ReturnHeader::withTrashed()
            ->where('code', 'like', "RTN/{$yearMonth}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -4) + 1 : 1;

        return "RTN/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate AR payment code.
     *
     * Format: FK/{company.payment_code}/YYMM/00001
     *
     * @param  int  $companyId  The company ID to fetch the 2-digit payment_code
     * @return string The generated payment code
     */
    public static function generatePaymentCode(int $companyId): string
    {
        $company = Company::findOrFail($companyId);
        $paymentCode = $company->payment_code ?? '00';
        $yearMonth = date('ym');
        $prefix = "FK/{$paymentCode}/{$yearMonth}";

        $last = ArPaymentHeader::withTrashed()
            ->where('code', 'like', "{$prefix}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -5) + 1 : 1;

        return "{$prefix}/".str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
