<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\ItemCategory;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'STRAW',
                'name' => 'Straw',
                'remarks' => 'Sedotan plastik berbagai ukuran dan jenis',
            ],
            [
                'code' => 'CUP',
                'name' => 'Cup',
                'remarks' => 'Gelas plastik untuk minuman',
            ],
            [
                'code' => 'BAG',
                'name' => 'Bag',
                'remarks' => 'Kantong plastik berbagai ukuran',
            ],
            [
                'code' => 'CONTAINER',
                'name' => 'Container',
                'remarks' => 'Wadah plastik untuk makanan dan penyimpanan',
            ],
            [
                'code' => 'SHEET',
                'name' => 'Sheet & Film',
                'remarks' => 'Lembaran dan film plastik PP/PE',
            ],
            [
                'code' => 'RM',
                'name' => 'Raw Material',
                'remarks' => 'Bahan baku seperti biji plastik PP, PE, dan resin',
            ],
            [
                'code' => 'SCRAP',
                'name' => 'Scrap & Waste',
                'remarks' => 'Sisa produksi, barang reject, atau waste plastik',
            ],
        ];

        foreach ($data as $item) {
            try {
                ItemCategory::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'remarks' => $item['remarks'],
                        'is_active' => true,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]
                );
            } catch (QueryException $e) {
                $this->command->error("Failed to seed item category: {$item['code']} - {$e->getMessage()}");
            } catch (Exception $e) {
                $this->command->error("Unexpected error seeding item category: {$item['code']} - {$e->getMessage()}");
            }
        }
    }
}
