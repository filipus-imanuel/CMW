<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class UserGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('user_groups')->insert([
            [
                'code' => 'SUPER_ADMIN',
                'name' => 'Super Administrator',
                'remarks' => 'Akses penuh seluruh modul dan konfigurasi sistem',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Finance & Accounting',
                'remarks' => 'Pengelolaan keuangan, jurnal, COA, dan pajak',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SALES',
                'name' => 'Sales',
                'remarks' => 'Penjualan, sales order, invoice, dan piutang',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PURCHASING',
                'name' => 'Purchasing',
                'remarks' => 'Pembelian, supplier, dan purchase order',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'WAREHOUSE',
                'name' => 'Warehouse',
                'remarks' => 'Manajemen stok, gudang, dan pengiriman',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MANAGEMENT',
                'name' => 'Management',
                'remarks' => 'Akses laporan dan dashboard (read-only)',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Admin',
                'remarks' => 'Pengelolaan master data dan administrasi sistem',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
