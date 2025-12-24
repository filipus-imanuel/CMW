<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CoaSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil COA ID berdasarkan code (AMAN & ERP-grade)
        $coas = DB::table('coas')
            ->whereIn('code', [
                '1001', // Kas
                '1002', // Bank
                '1101', // Piutang Usaha
                '1201', // Persediaan Bahan Baku
                '1202', // Persediaan Barang Jadi
                '1301', // Aset Tetap
                '2001', // Hutang Usaha
                '2101', // Hutang Pajak
                '3001', // Modal Disetor
                '4001', // Penjualan
                '5001', // HPP
                '6003', // Beban Penyusutan
            ])
            ->pluck('id', 'code');

        DB::table('coa_settings')->insert([
            [
                'code' => 'CASH_ACCOUNT',
                'name' => 'Akun Kas',
                'coa_id' => $coas['1001'],
                'remarks' => 'Akun kas default untuk transaksi tunai',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BANK_ACCOUNT',
                'name' => 'Akun Bank',
                'coa_id' => $coas['1002'],
                'remarks' => 'Akun bank default',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'AR_ACCOUNT',
                'name' => 'Piutang Usaha',
                'coa_id' => $coas['1101'],
                'remarks' => 'Akun piutang customer',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'AP_ACCOUNT',
                'name' => 'Hutang Usaha',
                'coa_id' => $coas['2001'],
                'remarks' => 'Akun hutang supplier',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'INVENTORY_RM',
                'name' => 'Persediaan Bahan Baku',
                'coa_id' => $coas['1201'],
                'remarks' => 'Akun persediaan bahan baku',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'INVENTORY_FG',
                'name' => 'Persediaan Barang Jadi',
                'coa_id' => $coas['1202'],
                'remarks' => 'Akun persediaan barang jadi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SALES_ACCOUNT',
                'name' => 'Akun Penjualan',
                'coa_id' => $coas['4001'],
                'remarks' => 'Pendapatan penjualan produk',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'COGS_ACCOUNT',
                'name' => 'Akun HPP',
                'coa_id' => $coas['5001'],
                'remarks' => 'Harga pokok penjualan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'TAX_PAYABLE',
                'name' => 'Hutang Pajak',
                'coa_id' => $coas['2101'],
                'remarks' => 'PPN dan pajak terutang',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ASSET_ACCOUNT',
                'name' => 'Aset Tetap',
                'coa_id' => $coas['1301'],
                'remarks' => 'Akun aset tetap',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'DEPRECIATION_EXP',
                'name' => 'Beban Penyusutan',
                'coa_id' => $coas['6003'],
                'remarks' => 'Beban penyusutan aset',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAPITAL_ACCOUNT',
                'name' => 'Modal Disetor',
                'coa_id' => $coas['3001'],
                'remarks' => 'Modal awal pemilik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
