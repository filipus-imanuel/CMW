<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\Item;
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
                'type' => 'finished_goods',
                'uom_id' => $uoms['LEMBAR'],
                'category_id' => 2,
                'cost_price' => 8500,
                'sell_price' => 11000,
                'min_stock' => 100,
                'max_stock' => 5000,
                'remarks' => 'Produk potong PP film ukuran 12 x 12 cm',
            ],
            [
                'code' => 'ITM-FLM-PP-002',
                'name' => 'PP Film 12 x 13 cm',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['ROLL'],
                'cost_price' => 420000,
                'sell_price' => 480000,
                'min_stock' => 10,
                'max_stock' => 200,
                'remarks' => 'PP film dalam bentuk roll',
            ],
            [
                'code' => 'ITM-STRETCH-001',
                'name' => 'Stretch Film Industri',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['KG'],
                'cost_price' => 18500,
                'sell_price' => 23000,
                'min_stock' => 200,
                'max_stock' => 10000,
                'remarks' => 'Stretch film untuk kebutuhan industri',
            ],
            [
                'code' => 'ITM-WRAP-001',
                'name' => 'Plastic Wrap Lembaran',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['LEMBAR'],
                'cost_price' => 3000,
                'sell_price' => 4500,
                'min_stock' => 0,
                'max_stock' => 0,
                'remarks' => 'Produk wrap lama (tidak aktif)',
                'is_active' => false,
            ],
            [
                'code' => 'ITM-KLIP-STD-001',
                'name' => 'Klip Plastik Standar',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['ROLL'],
                'cost_price' => 150000,
                'sell_price' => 195000,
                'min_stock' => 20,
                'max_stock' => 1000,
                'remarks' => 'Klip plastik untuk kemasan',
            ],
        ];

        foreach ($data as $item) {
            try {
                Item::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'type' => $item['type'],
                        'uom_id' => $item['uom_id'],
                        'category_id' => $item['category_id'],
                        'cost_price' => $item['cost_price'],
                        'sell_price' => $item['sell_price'],
                        'min_stock' => $item['min_stock'],
                        'max_stock' => $item['max_stock'],
                        'remarks' => $item['remarks'],
                        'is_active' => $item['is_active'] ?? true,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]
                );
            } catch (QueryException $e) {
                $this->command->error("Failed to seed item: {$item['code']} - {$e->getMessage()}");
            } catch (Exception $e) {
                $this->command->error("Unexpected error seeding item: {$item['code']} - {$e->getMessage()}");
            }
        }
    }
}
