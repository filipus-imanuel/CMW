<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Transaction\OrderHeader;
use Illuminate\Support\Facades\DB;

class CustomerCheckHelper
{
    /**
     * Get total outstanding balance (unpaid AR invoices) for a customer.
     */
    public static function getOutstandingBalance(int $partnerId): float
    {
        return (float) DB::table('ar_invoice_headers')
            ->where('partner_id', $partnerId)
            ->whereNull('deleted_at')
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('balance');
    }

    /**
     * Get total of pending orders (not yet invoiced) for a customer.
     * Statuses: APPROVAL, ORDER, DELIVERY.
     */
    public static function getPendingOrdersTotal(int $partnerId, ?int $excludeOrderId = null): float
    {
        return (float) OrderHeader::where('partner_id', $partnerId)
            ->whereIn('status', ['APPROVAL', 'ORDER', 'DELIVERY'])
            ->when($excludeOrderId, fn ($q) => $q->where('id', '!=', $excludeOrderId))
            ->sum('total');
    }

    /**
     * Check if customer debt exceeds their credit limit.
     * Projected = AR outstanding + pending orders total + current order total.
     *
     * @return array{exceeded: bool, outstanding: float, pending_orders: float, current_order: float, projected: float, limit: float, remaining: float|null}
     */
    public static function hasExcessiveDebt(int $partnerId, float $currentOrderTotal = 0, ?int $excludeOrderId = null): array
    {
        $partner = Partner::findOrFail($partnerId);
        $outstanding = self::getOutstandingBalance($partnerId);
        $pendingOrders = self::getPendingOrdersTotal($partnerId, $excludeOrderId);
        $projected = $outstanding + $pendingOrders + $currentOrderTotal;
        $limit = (float) $partner->credit_limit;

        return [
            'exceeded' => $limit > 0 && $projected > $limit,
            'outstanding' => $outstanding,
            'pending_orders' => $pendingOrders,
            'current_order' => $currentOrderTotal,
            'projected' => $projected,
            'limit' => $limit,
            'remaining' => $limit > 0 ? round($limit - $projected, 2) : null,
        ];
    }

    /**
     * Count pending deliveries (orders not yet finished) for a customer.
     */
    public static function getPendingDeliveries(int $partnerId, ?int $excludeOrderId = null): int
    {
        return OrderHeader::where('partner_id', $partnerId)
            ->whereNotIn('status', ['INIT', 'FINISH', 'FINAL', 'REJECTED'])
            ->when($excludeOrderId, fn ($q) => $q->where('id', '!=', $excludeOrderId))
            ->count();
    }

    /**
     * Check if adding an amount to a company's category would exceed the sales limit.
     *
     * @return array{exceeded: bool, allowed: bool, current: float, limit: float, after: float, reason: string|null}
     */
    public static function checkCompanyCategoryLimit(int $companyId, int $categoryId, float $additionalAmount = 0): array
    {
        $company = Company::with('itemCategories')->findOrFail($companyId);

        // Check if company can handle this category
        if (! $company->itemCategories->contains('id', $categoryId)) {
            return [
                'exceeded' => true,
                'allowed' => false,
                'current' => 0,
                'limit' => (float) $company->sales_limit,
                'after' => $additionalAmount,
                'reason' => 'Company is not authorized for this item category',
            ];
        }

        // Calculate current total from active orders for this company
        $currentTotal = (float) OrderHeader::where('company_id', $companyId)
            ->whereIn('status', ['ORDER', 'DELIVERY'])
            ->sum('total');

        $afterTotal = $currentTotal + $additionalAmount;
        $limit = (float) $company->sales_limit;

        return [
            'exceeded' => $limit > 0 && $afterTotal > $limit,
            'allowed' => $limit <= 0 || $afterTotal <= $limit,
            'current' => $currentTotal,
            'limit' => $limit,
            'after' => $afterTotal,
            'reason' => null,
        ];
    }

    /**
     * Run all checks for a customer/company/category combination.
     *
     * @return array{debt: array, deliveries: int, limit: array, has_issues: bool}
     */
    public static function runAllChecks(int $partnerId, int $companyId, int $categoryId, float $amount = 0, ?int $excludeOrderId = null): array
    {
        $debt = self::hasExcessiveDebt($partnerId, $amount, $excludeOrderId);
        $deliveries = self::getPendingDeliveries($partnerId, $excludeOrderId);
        $limit = self::checkCompanyCategoryLimit($companyId, $categoryId, $amount);

        return [
            'debt' => $debt,
            'deliveries' => $deliveries,
            'limit' => $limit,
            'has_issues' => $debt['exceeded'] || $deliveries > 0 || $limit['exceeded'],
        ];
    }
}
