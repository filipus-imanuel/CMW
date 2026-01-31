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
                'code' => 'OTHER',
                'name' => 'Other',
                'remarks' => 'Kategori lain-lain',
            ],
            [
                'code' => 'RM',
                'name' => 'Raw Material',
                'remarks' => 'Bahan baku utama seperti PP, PE, dan resin lainnya',
            ],
            [
                'code' => 'FG',
                'name' => 'Finished Goods',
                'remarks' => 'Barang jadi siap dijual ke customer',
            ],
            [
                'code' => 'SF',
                'name' => 'Semi Finished Goods',
                'remarks' => 'Barang setengah jadi hasil proses produksi',
            ],
            [
                'code' => 'PACK',
                'name' => 'Packing Material',
                'remarks' => 'Material pendukung kemasan seperti plastik wrap, karton, dll',
            ],
            [
                'code' => 'SCRAP',
                'name' => 'Scrap & Waste',
                'remarks' => 'Sisa produksi, barang rusak, atau waste',
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
