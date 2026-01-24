<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * Ambil department_id berdasarkan code
         * (AMAN untuk ERP, tidak hardcode ID)
         */
        $departments = DB::table('departments')
            ->whereIn('code', ['DIR', 'FIN', 'PUR', 'WH', 'SAL'])
            ->pluck('id', 'code');

        if ($departments->count() < 5) {
            throw new Exception('Seeder employees gagal: data departments belum lengkap.');
        }

        DB::table('employees')->insert([
            [
                'code' => 'EMP-001',
                'name' => 'Andi Pratama',
                'department_id' => $departments['DIR'],
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
                'department_id' => $departments['FIN'],
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
                'department_id' => $departments['PUR'],
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
                'department_id' => $departments['WH'],
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
                'department_id' => $departments['SAL'],
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
