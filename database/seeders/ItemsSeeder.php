<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\Item;
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
        $uoms = Uom::whereIn('code', ['LEMBAR', 'ROLL', 'KG'])
            ->pluck('id', 'code');

        $data = [
            [
                'code' => 'ITM-FLM-PP-001',
                'name' => 'PP Film 12 x 12 cm',
                'type' => 'FINISHED_GOOD',
                'base_uom_code' => 'LEMBAR',
                'item_category_id' => 2,
                'cost_price' => 8500,
                'sell_price' => 11000,
                'min_stock' => 100,
                'max_stock' => 5000,
                'remarks' => 'Produk potong PP film ukuran 12 x 12 cm',
                'extra_uoms' => [
                    ['uom_code' => 'ROLL', 'conversion_rate' => 500],
                ],
            ],
            [
                'code' => 'ITM-FLM-PP-002',
                'name' => 'PP Film 12 x 13 cm',
                'type' => 'FINISHED_GOOD',
                'item_category_id' => 2,
                'base_uom_code' => 'ROLL',
                'cost_price' => 420000,
                'sell_price' => 480000,
                'min_stock' => 10,
                'max_stock' => 200,
                'remarks' => 'PP film dalam bentuk roll',
                'extra_uoms' => [],
            ],
            [
                'code' => 'ITM-STRETCH-001',
                'name' => 'Stretch Film Industri',
                'type' => 'FINISHED_GOOD',
                'item_category_id' => 2,
                'base_uom_code' => 'KG',
                'cost_price' => 18500,
                'sell_price' => 23000,
                'min_stock' => 200,
                'max_stock' => 10000,
                'remarks' => 'Stretch film untuk kebutuhan industri',
                'extra_uoms' => [
                    ['uom_code' => 'ROLL', 'conversion_rate' => 25],
                ],
            ],
            [
                'code' => 'ITM-WRAP-001',
                'name' => 'Plastic Wrap Lembaran',
                'type' => 'FINISHED_GOOD',
                'item_category_id' => 2,
                'base_uom_code' => 'LEMBAR',
                'cost_price' => 3000,
                'sell_price' => 4500,
                'min_stock' => 0,
                'max_stock' => 0,
                'remarks' => 'Produk wrap lama (tidak aktif)',
                'is_active' => false,
                'extra_uoms' => [],
            ],
            [
                'code' => 'ITM-KLIP-STD-001',
                'name' => 'Klip Plastik Standar',
                'type' => 'FINISHED_GOOD',
                'item_category_id' => 2,
                'base_uom_code' => 'ROLL',
                'cost_price' => 150000,
                'sell_price' => 195000,
                'min_stock' => 20,
                'max_stock' => 1000,
                'remarks' => 'Klip plastik untuk kemasan',
                'extra_uoms' => [],
            ],
        ];

        foreach ($data as $itemData) {
            try {
                $baseUomCode = $itemData['base_uom_code'];
                $extraUoms = $itemData['extra_uoms'] ?? [];
                unset($itemData['base_uom_code'], $itemData['extra_uoms']);

                $item = Item::updateOrCreate(
                    ['code' => $itemData['code']],
                    [
                        'name' => $itemData['name'],
                        'type' => $itemData['type'],
                        'item_category_id' => $itemData['item_category_id'],
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
            } catch (QueryException $e) {
                $this->command->error("Failed to seed item: {$itemData['code']} - {$e->getMessage()}");
            } catch (Exception $e) {
                $this->command->error("Unexpected error seeding item: {$itemData['code']} - {$e->getMessage()}");
            }
        }
    }
}
