<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('positions')->insert([
            [
                'code' => 'DIR',
                'name' => 'Director',
                'remarks' => 'Top management / pengambil keputusan utama',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance Manager',
                'remarks' => 'Bertanggung jawab atas keuangan dan pajak',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PUR',
                'name' => 'Purchasing Officer',
                'remarks' => 'Pengadaan bahan baku dan barang',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'WH',
                'name' => 'Warehouse Supervisor',
                'remarks' => 'Pengelolaan gudang dan stock opname',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SAL',
                'name' => 'Sales Executive',
                'remarks' => 'Penjualan dan relasi customer',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
