<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil user group ID (AMAN, tidak hardcode)
        $groups = DB::table('user_groups')
            ->whereIn('code', [
                'SUPER_ADMIN',
                'ADMIN',
                'FINANCE',
                'SALES',
                'PURCHASING',
                'WAREHOUSE',
                'MANAGEMENT',
            ])
            ->pluck('id', 'code');

        /**
         * ======================
         * TOP LEVEL MENUS
         * ======================
         */
        DB::table('menus')->insert([
            [
                'code' => 'DASHBOARD',
                'name' => 'Dashboard',
                'parent_id' => null,
                'user_group_id' => null, // visible untuk semua
                'url' => '/dashboard',
                'icon' => 'home',
                'order' => 1,
                'remarks' => 'Dashboard utama',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MASTER_DATA',
                'name' => 'Master Data',
                'parent_id' => null,
                'user_group_id' => $groups['ADMIN'],
                'url' => null,
                'icon' => 'database',
                'order' => 2,
                'remarks' => 'Master data sistem',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'INVENTORY',
                'name' => 'Inventory',
                'parent_id' => null,
                'user_group_id' => $groups['WAREHOUSE'],
                'url' => null,
                'icon' => 'box',
                'order' => 3,
                'remarks' => 'Manajemen stok',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PURCHASING',
                'name' => 'Purchasing',
                'parent_id' => null,
                'user_group_id' => $groups['PURCHASING'],
                'url' => null,
                'icon' => 'shopping-cart',
                'order' => 4,
                'remarks' => 'Pembelian',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SALES',
                'name' => 'Sales',
                'parent_id' => null,
                'user_group_id' => $groups['SALES'],
                'url' => null,
                'icon' => 'trending-up',
                'order' => 5,
                'remarks' => 'Penjualan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Finance',
                'parent_id' => null,
                'user_group_id' => $groups['FINANCE'],
                'url' => null,
                'icon' => 'dollar-sign',
                'order' => 6,
                'remarks' => 'Keuangan & akuntansi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'REPORTS',
                'name' => 'Reports',
                'parent_id' => null,
                'user_group_id' => $groups['MANAGEMENT'],
                'url' => null,
                'icon' => 'bar-chart',
                'order' => 7,
                'remarks' => 'Laporan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SETTINGS',
                'name' => 'Settings',
                'parent_id' => null,
                'user_group_id' => $groups['SUPER_ADMIN'],
                'url' => null,
                'icon' => 'settings',
                'order' => 8,
                'remarks' => 'Pengaturan sistem',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        /**
         * ======================
         * SUB MENUS
         * ======================
         */
        $menus = DB::table('menus')->pluck('id', 'code');

        DB::table('menus')->insert([
            // MASTER DATA
            [
                'code' => 'MD_ITEMS',
                'name' => 'Items',
                'parent_id' => $menus['MASTER_DATA'],
                'user_group_id' => $groups['ADMIN'],
                'url' => '/items',
                'icon' => 'package',
                'order' => 1,
                'remarks' => 'Master item',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MD_PARTNERS',
                'name' => 'Partners',
                'parent_id' => $menus['MASTER_DATA'],
                'user_group_id' => $groups['ADMIN'],
                'url' => '/partners',
                'icon' => 'users',
                'order' => 2,
                'remarks' => 'Supplier & customer',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MD_WAREHOUSES',
                'name' => 'Warehouses',
                'parent_id' => $menus['MASTER_DATA'],
                'user_group_id' => $groups['ADMIN'],
                'url' => '/warehouses',
                'icon' => 'map-pin',
                'order' => 3,
                'remarks' => 'Gudang',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // INVENTORY
            [
                'code' => 'INV_STOCK_IN',
                'name' => 'Stock In',
                'parent_id' => $menus['INVENTORY'],
                'user_group_id' => $groups['WAREHOUSE'],
                'url' => '/inventory/stock-in',
                'icon' => 'arrow-down',
                'order' => 1,
                'remarks' => 'Barang masuk',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'INV_STOCK_OUT',
                'name' => 'Stock Out',
                'parent_id' => $menus['INVENTORY'],
                'user_group_id' => $groups['WAREHOUSE'],
                'url' => '/inventory/stock-out',
                'icon' => 'arrow-up',
                'order' => 2,
                'remarks' => 'Barang keluar',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // FINANCE
            [
                'code' => 'FIN_COA',
                'name' => 'Chart of Accounts',
                'parent_id' => $menus['FINANCE'],
                'user_group_id' => $groups['FINANCE'],
                'url' => '/finance/coas',
                'icon' => 'list',
                'order' => 1,
                'remarks' => 'Daftar akun',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FIN_JOURNAL',
                'name' => 'Journal',
                'parent_id' => $menus['FINANCE'],
                'user_group_id' => $groups['FINANCE'],
                'url' => '/finance/journals',
                'icon' => 'book',
                'order' => 2,
                'remarks' => 'Jurnal umum',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // SETTINGS
            [
                'code' => 'SET_USERS',
                'name' => 'Users',
                'parent_id' => $menus['SETTINGS'],
                'user_group_id' => $groups['SUPER_ADMIN'],
                'url' => '/settings/users',
                'icon' => 'user',
                'order' => 1,
                'remarks' => 'Manajemen user',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SET_MENUS',
                'name' => 'Menus',
                'parent_id' => $menus['SETTINGS'],
                'user_group_id' => $groups['SUPER_ADMIN'],
                'url' => '/settings/menus',
                'icon' => 'menu',
                'order' => 2,
                'remarks' => 'Manajemen menu',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
