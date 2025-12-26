<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('taxes')->insert([
            [
                'code' => 'PPN11',
                'name' => 'PPN 11%',
                'rate' => 11.0000,
                'remarks' => 'Pajak Pertambahan Nilai sesuai regulasi Indonesia',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PPN0',
                'name' => 'PPN 0%',
                'rate' => 0.0000,
                'remarks' => 'PPN 0% (ekspor atau fasilitas khusus)',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'NONPPN',
                'name' => 'Non PPN',
                'rate' => 0.0000,
                'remarks' => 'Transaksi tidak dikenakan PPN',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PPH23',
                'name' => 'PPh Pasal 23',
                'rate' => 2.0000,
                'remarks' => 'PPh Pasal 23 atas jasa tertentu',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PPH22',
                'name' => 'PPh Pasal 22',
                'rate' => 1.5000,
                'remarks' => 'PPh Pasal 22 atas pembelian tertentu',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
