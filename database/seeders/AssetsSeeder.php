<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('assets')->insert([
            [
                'code' => 'AST-MCH-001',
                'name' => 'Mesin Extruder Plastik',
                'acquisition_date' => '2022-03-15',
                'acquisition_cost' => 1200000000.00,
                'current_value' => 950000000.00,
                'remarks' => 'Mesin utama untuk proses extruding biji plastik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'AST-MCH-002',
                'name' => 'Mesin Cutting Plastik',
                'acquisition_date' => '2022-07-10',
                'acquisition_cost' => 350000000.00,
                'current_value' => 280000000.00,
                'remarks' => 'Mesin pemotong plastik lembaran dan roll',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'AST-VHC-001',
                'name' => 'Truk Box Distribusi',
                'acquisition_date' => '2021-11-01',
                'acquisition_cost' => 450000000.00,
                'current_value' => 300000000.00,
                'remarks' => 'Kendaraan distribusi barang jadi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'AST-IT-001',
                'name' => 'Server ERP',
                'acquisition_date' => '2023-01-20',
                'acquisition_cost' => 180000000.00,
                'current_value' => 150000000.00,
                'remarks' => 'Server utama sistem ERP dan database',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'AST-OFF-001',
                'name' => 'Peralatan Kantor',
                'acquisition_date' => '2021-05-05',
                'acquisition_cost' => 95000000.00,
                'current_value' => 40000000.00,
                'remarks' => 'Meja, kursi, lemari, dan perlengkapan kantor',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
