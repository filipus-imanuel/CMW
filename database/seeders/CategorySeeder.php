<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('categories')->insert([
            [
                'code' => 'CAT-RM',
                'name' => 'Raw Material',
                'remarks' => 'Bahan baku utama seperti PP, PE, dan resin lainnya',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAT-FG',
                'name' => 'Finished Goods',
                'remarks' => 'Barang jadi siap dijual ke customer',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAT-SF',
                'name' => 'Semi Finished Goods',
                'remarks' => 'Barang setengah jadi hasil proses produksi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAT-PACK',
                'name' => 'Packing Material',
                'remarks' => 'Material pendukung kemasan seperti plastik wrap, karton, dll',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAT-SCRAP',
                'name' => 'Scrap & Waste',
                'remarks' => 'Sisa produksi, barang rusak, atau waste',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAT-OTHER',
                'name' => 'Other',
                'remarks' => 'Kategori lain-lain',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
