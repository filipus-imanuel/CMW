<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('item_categories')->insert([[
            'code' => 'OTHER',
            'name' => 'Other',
            'remarks' => 'Kategori lain-lain',
            'is_active' => true,
            'version_number' => 1,
            'created_by' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ],
            [
                'code' => 'RM',
                'name' => 'Raw Material',
                'remarks' => 'Bahan baku utama seperti PP, PE, dan resin lainnya',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FG',
                'name' => 'Finished Goods',
                'remarks' => 'Barang jadi siap dijual ke customer',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SF',
                'name' => 'Semi Finished Goods',
                'remarks' => 'Barang setengah jadi hasil proses produksi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PACK',
                'name' => 'Packing Material',
                'remarks' => 'Material pendukung kemasan seperti plastik wrap, karton, dll',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SCRAP',
                'name' => 'Scrap & Waste',
                'remarks' => 'Sisa produksi, barang rusak, atau waste',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
