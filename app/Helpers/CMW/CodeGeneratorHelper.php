<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Transaction\OrderHeader;

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

        $last = OrderHeader::withTrashed()
            ->where('code', 'like', "{$prefix}/{$yearMonth}/%")
            ->orderByDesc('code')
            ->first();

        $number = $last ? (int) substr($last->code, -4) + 1 : 1;

        return "{$prefix}/{$yearMonth}/".str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
