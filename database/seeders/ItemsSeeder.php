<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Uom;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get UOM IDs by code
        $uoms = Uom::whereIn('code', ['KG', 'ROLL'])
            ->pluck('id', 'code');

        $categories = ItemCategory::pluck('id', 'code');
        $categoryPrices = CategoryPrice::pluck('id', 'code');

        $data = [
            [
                'code' => 'ITM-STR-001',
                'name' => 'Sedotan Lurus 6mm',
                'type' => 'FINISHED_GOOD',
                'base_uom_code' => 'KG',
                'item_category_code' => 'STRAW',
                'cost_price' => 12000,
                'sell_price' => 16000,
                'min_stock' => 100,
                'max_stock' => 5000,
                'remarks' => 'Sedotan plastik lurus diameter 6mm',
                'extra_uoms' => [],
                'prices' => [
                    'KG' => ['GEN' => 16000, 'VIP' => 14500],
                ],
            ],
            [
                'code' => 'ITM-STR-002',
                'name' => 'Sedotan Tekuk 12mm',
                'type' => 'FINISHED_GOOD',
                'base_uom_code' => 'KG',
                'item_category_code' => 'STRAW',
                'cost_price' => 14000,
                'sell_price' => 18500,
                'min_stock' => 50,
                'max_stock' => 3000,
                'remarks' => 'Sedotan plastik bengkok diameter 12mm',
                'extra_uoms' => [],
                'prices' => [
                    'KG' => ['GEN' => 18500, 'VIP' => 16800],
                ],
            ],
            [
                'code' => 'ITM-CUP-001',
                'name' => 'Gelas Plastik PP 220ml',
                'type' => 'FINISHED_GOOD',
                'base_uom_code' => 'KG',
                'item_category_code' => 'CUP',
                'cost_price' => 25000,
                'sell_price' => 32000,
                'min_stock' => 200,
                'max_stock' => 10000,
                'remarks' => 'Gelas plastik PP transparan 220ml',
                'extra_uoms' => [],
                'prices' => [
                    'KG' => ['GEN' => 32000, 'VIP' => 29000],
                ],
            ],
            [
                'code' => 'ITM-BAG-001',
                'name' => 'Kantong Plastik HD 24x38',
                'type' => 'FINISHED_GOOD',
                'base_uom_code' => 'KG',
                'item_category_code' => 'BAG',
                'cost_price' => 18000,
                'sell_price' => 23000,
                'min_stock' => 100,
                'max_stock' => 8000,
                'remarks' => 'Kantong plastik HD ukuran 24 x 38 cm',
                'extra_uoms' => [
                    ['uom_code' => 'ROLL', 'conversion_rate' => 50],
                ],
                'prices' => [
                    'KG' => ['GEN' => 23000, 'VIP' => 21000],
                    'ROLL' => ['GEN' => 1150000, 'VIP' => 1050000],
                ],
            ],
            [
                'code' => 'ITM-SHT-001',
                'name' => 'PP Sheet Roll 0.5mm',
                'type' => 'FINISHED_GOOD',
                'base_uom_code' => 'KG',
                'item_category_code' => 'SHEET',
                'cost_price' => 21000,
                'sell_price' => 27000,
                'min_stock' => 50,
                'max_stock' => 5000,
                'remarks' => 'Lembaran PP tebal 0.5mm dalam roll',
                'extra_uoms' => [
                    ['uom_code' => 'ROLL', 'conversion_rate' => 25],
                ],
                'prices' => [
                    'KG' => ['GEN' => 27000, 'VIP' => 24500],
                    'ROLL' => ['GEN' => 675000, 'VIP' => 612500],
                ],
            ],
            [
                'code' => 'ITM-RM-PP-001',
                'name' => 'Biji Plastik PP Homopolymer',
                'type' => 'RAW_MATERIAL',
                'base_uom_code' => 'KG',
                'item_category_code' => 'RM',
                'cost_price' => 15000,
                'sell_price' => 0,
                'min_stock' => 500,
                'max_stock' => 20000,
                'remarks' => 'Bahan baku biji plastik Polypropylene',
                'extra_uoms' => [],
                'prices' => [
                    'KG' => ['GEN' => 15000, 'VIP' => 13500],
                ],
            ],
            [
                'code' => 'ITM-RM-PE-001',
                'name' => 'Biji Plastik PE HDPE',
                'type' => 'RAW_MATERIAL',
                'base_uom_code' => 'KG',
                'item_category_code' => 'RM',
                'cost_price' => 16500,
                'sell_price' => 0,
                'min_stock' => 500,
                'max_stock' => 20000,
                'remarks' => 'Bahan baku biji plastik High Density PE',
                'extra_uoms' => [],
                'prices' => [
                    'KG' => ['GEN' => 16500, 'VIP' => 15000],
                ],
            ],
        ];

        foreach ($data as $itemData) {
            try {
                $baseUomCode = $itemData['base_uom_code'];
                $extraUoms = $itemData['extra_uoms'] ?? [];
                $categoryCode = $itemData['item_category_code'];
                $prices = $itemData['prices'] ?? [];
                unset($itemData['base_uom_code'], $itemData['extra_uoms'], $itemData['item_category_code'], $itemData['prices']);

                $item = Item::updateOrCreate(
                    ['code' => $itemData['code']],
                    [
                        'name' => $itemData['name'],
                        'type' => $itemData['type'],
                        'item_category_id' => $categories[$categoryCode],
                        'cost_price' => $itemData['cost_price'],
                        'sell_price' => $itemData['sell_price'],
                        'min_stock' => $itemData['min_stock'],
                        'max_stock' => $itemData['max_stock'],
                        'remarks' => $itemData['remarks'],
                        'is_active' => $itemData['is_active'] ?? true,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]
                );

                // Create base UOM
                $baseUomId = $uoms[$baseUomCode];
                ItemUom::updateOrCreate(
                    ['item_id' => $item->id, 'uom_id' => $baseUomId],
                    [
                        'conversion_rate' => 1.0000,
                        'is_base' => true,
                        'is_active' => true,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]
                );

                // Create extra UOMs
                foreach ($extraUoms as $extra) {
                    $extraUomId = $uoms[$extra['uom_code']];
                    ItemUom::updateOrCreate(
                        ['item_id' => $item->id, 'uom_id' => $extraUomId],
                        [
                            'conversion_rate' => $extra['conversion_rate'],
                            'is_base' => false,
                            'is_active' => true,
                            'created_by' => 1,
                            'updated_by' => 1,
                        ]
                    );
                }

                // Create item prices per UOM per category price
                foreach ($prices as $uomCode => $categoryPriceMap) {
                    $itemUom = ItemUom::where('item_id', $item->id)
                        ->where('uom_id', $uoms[$uomCode])
                        ->first();

                    foreach ($categoryPriceMap as $cpCode => $price) {
                        ItemPrice::updateOrCreate(
                            ['item_uom_id' => $itemUom->id, 'category_price_id' => $categoryPrices[$cpCode]],
                            [
                                'price' => $price,
                                'is_active' => true,
                                'created_by' => 1,
                                'updated_by' => 1,
                            ]
                        );
                    }
                }
            } catch (QueryException $e) {
                $this->command->error("Failed to seed item: {$itemData['code']} - {$e->getMessage()}");
            } catch (Exception $e) {
                $this->command->error("Unexpected error seeding item: {$itemData['code']} - {$e->getMessage()}");
            }
        }
    }
}
