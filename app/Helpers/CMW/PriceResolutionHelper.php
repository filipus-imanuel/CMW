<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Master\Partner;

class PriceResolutionHelper
{
    /**
     * Resolve the selling price for an item given a customer (partner).
     *
     * Fallback order:
     * 1. ItemPrice matching item + customer's category_price_id
     * 2. ItemPrice matching item + GEN (general) category price
     * 3. Item.sell_price (master default)
     *
     * @return array{price: float, source: string, category_price_id: int|null}
     */
    public static function resolve(Item $item, ?Partner $partner = null): array
    {
        // 1. Try customer-specific category price
        if ($partner && $partner->category_price_id) {
            $itemPrice = ItemPrice::where('item_id', $item->id)
                ->where('category_price_id', $partner->category_price_id)
                ->where('is_active', true)
                ->first();

            if ($itemPrice) {
                return [
                    'price' => (float) $itemPrice->price,
                    'source' => 'customer_category',
                    'category_price_id' => $itemPrice->category_price_id,
                ];
            }
        }

        // 2. Fallback to GEN (general) category price
        $genCategory = CategoryPrice::where('code', 'GEN')
            ->where('is_active', true)
            ->first();

        if ($genCategory) {
            $genPrice = ItemPrice::where('item_id', $item->id)
                ->where('category_price_id', $genCategory->id)
                ->where('is_active', true)
                ->first();

            if ($genPrice) {
                return [
                    'price' => (float) $genPrice->price,
                    'source' => 'general_category',
                    'category_price_id' => $genCategory->id,
                ];
            }
        }

        // 3. Final fallback to item master sell_price
        return [
            'price' => (float) $item->sell_price,
            'source' => 'item_sell_price',
            'category_price_id' => null,
        ];
    }

    /**
     * Resolve prices for multiple items at once (batch-optimized).
     *
     * @param  \Illuminate\Support\Collection<int, Item>  $items
     * @return array<int, array{price: float, source: string, category_price_id: int|null}> Keyed by item ID
     */
    public static function resolveMany($items, ?Partner $partner = null): array
    {
        $itemIds = $items->pluck('id')->toArray();
        $results = [];

        // Pre-load relevant item prices in bulk
        $query = ItemPrice::whereIn('item_id', $itemIds)
            ->where('is_active', true);

        $categoryPriceIds = [];

        if ($partner && $partner->category_price_id) {
            $categoryPriceIds[] = $partner->category_price_id;
        }

        $genCategory = CategoryPrice::where('code', 'GEN')
            ->where('is_active', true)
            ->first();

        if ($genCategory) {
            $categoryPriceIds[] = $genCategory->id;
        }

        if (! empty($categoryPriceIds)) {
            $query->whereIn('category_price_id', $categoryPriceIds);
        }

        $allPrices = $query->get()->groupBy('item_id');

        foreach ($items as $item) {
            $itemPrices = $allPrices->get($item->id, collect());

            // 1. Customer category
            if ($partner && $partner->category_price_id) {
                $customerPrice = $itemPrices->firstWhere('category_price_id', $partner->category_price_id);
                if ($customerPrice) {
                    $results[$item->id] = [
                        'price' => (float) $customerPrice->price,
                        'source' => 'customer_category',
                        'category_price_id' => $customerPrice->category_price_id,
                    ];

                    continue;
                }
            }

            // 2. GEN fallback
            if ($genCategory) {
                $genPrice = $itemPrices->firstWhere('category_price_id', $genCategory->id);
                if ($genPrice) {
                    $results[$item->id] = [
                        'price' => (float) $genPrice->price,
                        'source' => 'general_category',
                        'category_price_id' => $genCategory->id,
                    ];

                    continue;
                }
            }

            // 3. Item sell_price fallback
            $results[$item->id] = [
                'price' => (float) $item->sell_price,
                'source' => 'item_sell_price',
                'category_price_id' => null,
            ];
        }

        return $results;
    }
}
