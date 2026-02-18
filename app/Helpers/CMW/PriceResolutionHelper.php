<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Partner;

class PriceResolutionHelper
{
    /**
     * Resolve the selling price for an item given a customer (partner).
     *
     * Fallback order:
     * 1. ItemPrice matching item's base UOM (or specified UOM) + customer's category_price_id
     * 2. ItemPrice matching item's base UOM (or specified UOM) + GEN (general) category price
     * 3. Item.sell_price (master default)
     *
     * @param  int|null  $itemUomId  Specific ItemUom ID to resolve price for. Defaults to base UOM.
     * @return array{price: float, source: string, category_price_id: int|null, item_uom_id: int|null}
     */
    public static function resolve(Item $item, ?Partner $partner = null, ?int $itemUomId = null): array
    {
        // Determine which ItemUom to use
        if ($itemUomId) {
            $itemUom = ItemUom::where('id', $itemUomId)
                ->where('item_id', $item->id)
                ->first();
        } else {
            $itemUom = ItemUom::where('item_id', $item->id)
                ->where('is_base', true)
                ->first();
        }

        if (! $itemUom) {
            // No ItemUom found — fall back to item sell_price
            return [
                'price' => (float) $item->sell_price,
                'source' => 'item_sell_price',
                'category_price_id' => null,
                'item_uom_id' => null,
            ];
        }

        // 1. Try customer-specific category price
        if ($partner && $partner->category_price_id) {
            $itemPrice = ItemPrice::where('item_uom_id', $itemUom->id)
                ->where('category_price_id', $partner->category_price_id)
                ->where('is_active', true)
                ->first();

            if ($itemPrice) {
                return [
                    'price' => (float) $itemPrice->price,
                    'source' => 'customer_category',
                    'category_price_id' => $itemPrice->category_price_id,
                    'item_uom_id' => $itemUom->id,
                ];
            }
        }

        // 2. Fallback to GEN (general) category price
        $genCategory = CategoryPrice::where('code', 'GEN')
            ->where('is_active', true)
            ->first();

        if ($genCategory) {
            $genPrice = ItemPrice::where('item_uom_id', $itemUom->id)
                ->where('category_price_id', $genCategory->id)
                ->where('is_active', true)
                ->first();

            if ($genPrice) {
                return [
                    'price' => (float) $genPrice->price,
                    'source' => 'general_category',
                    'category_price_id' => $genCategory->id,
                    'item_uom_id' => $itemUom->id,
                ];
            }
        }

        // 3. Final fallback to item master sell_price
        return [
            'price' => (float) $item->sell_price,
            'source' => 'item_sell_price',
            'category_price_id' => null,
            'item_uom_id' => $itemUom->id,
        ];
    }

    /**
     * Resolve prices for multiple items at once (batch-optimized).
     * Uses the base UOM for each item.
     *
     * @param  \Illuminate\Support\Collection<int, Item>  $items
     * @return array<int, array{price: float, source: string, category_price_id: int|null, item_uom_id: int|null}> Keyed by item ID
     */
    public static function resolveMany($items, ?Partner $partner = null): array
    {
        $itemIds = $items->pluck('id')->toArray();
        $results = [];

        // Pre-load base ItemUoms for all items
        $baseUoms = ItemUom::whereIn('item_id', $itemIds)
            ->where('is_base', true)
            ->get()
            ->keyBy('item_id');

        $itemUomIds = $baseUoms->pluck('id')->toArray();

        // Pre-load relevant item prices in bulk
        $query = ItemPrice::whereIn('item_uom_id', $itemUomIds)
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

        $allPrices = $query->get()->groupBy('item_uom_id');

        foreach ($items as $item) {
            $baseUom = $baseUoms->get($item->id);

            if (! $baseUom) {
                $results[$item->id] = [
                    'price' => (float) $item->sell_price,
                    'source' => 'item_sell_price',
                    'category_price_id' => null,
                    'item_uom_id' => null,
                ];

                continue;
            }

            $itemPrices = $allPrices->get($baseUom->id, collect());

            // 1. Customer category
            if ($partner && $partner->category_price_id) {
                $customerPrice = $itemPrices->firstWhere('category_price_id', $partner->category_price_id);
                if ($customerPrice) {
                    $results[$item->id] = [
                        'price' => (float) $customerPrice->price,
                        'source' => 'customer_category',
                        'category_price_id' => $customerPrice->category_price_id,
                        'item_uom_id' => $baseUom->id,
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
                        'item_uom_id' => $baseUom->id,
                    ];

                    continue;
                }
            }

            // 3. Item sell_price fallback
            $results[$item->id] = [
                'price' => (float) $item->sell_price,
                'source' => 'item_sell_price',
                'category_price_id' => null,
                'item_uom_id' => $baseUom->id,
            ];
        }

        return $results;
    }
}
