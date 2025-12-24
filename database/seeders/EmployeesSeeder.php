<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * Ambil position_id berdasarkan code
         * (AMAN untuk ERP, tidak hardcode ID)
         */
        $positions = DB::table('positions')
            ->whereIn('code', ['DIR', 'FIN', 'PUR', 'WH', 'SAL'])
            ->pluck('id', 'code');

        if ($positions->count() < 5) {
            throw new \Exception('Seeder employees gagal: data positions belum lengkap.');
        }

        DB::table('employees')->insert([
            [
                'code' => 'EMP-001',
                'name' => 'Andi Pratama',
                'position_id' => $positions['DIR'],
                'email' => 'andi.pratama@company.co.id',
                'phone' => '0812-1111-2222',
                'address' => 'Jakarta Selatan',
                'remarks' => 'Direktur utama perusahaan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EMP-002',
                'name' => 'Siti Rahmawati',
                'position_id' => $positions['FIN'],
                'email' => 'siti.rahmawati@company.co.id',
                'phone' => '0813-2222-3333',
                'address' => 'Bekasi',
                'remarks' => 'Penanggung jawab keuangan & pajak',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EMP-003',
                'name' => 'Budi Santoso',
                'position_id' => $positions['PUR'],
                'email' => 'budi.santoso@company.co.id',
                'phone' => '0813-3333-4444',
                'address' => 'Tangerang',
                'remarks' => 'Purchasing bahan baku resin',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EMP-004',
                'name' => 'Rudi Hartono',
                'position_id' => $positions['WH'],
                'email' => 'rudi.hartono@company.co.id',
                'phone' => '0813-4444-5555',
                'address' => 'Jakarta Timur',
                'remarks' => 'Supervisor gudang & stock opname',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EMP-005',
                'name' => 'Dewi Lestari',
                'position_id' => $positions['SAL'],
                'email' => 'dewi.lestari@company.co.id',
                'phone' => '0813-5555-6666',
                'address' => 'Surabaya',
                'remarks' => 'Sales executive area Jawa Timur',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
