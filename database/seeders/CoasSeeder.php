<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('coas')->insert([
            // ======================
            // ASSETS (1xxx)
            // ======================
            [
                'code' => '1001',
                'name' => 'Kas',
                'remarks' => 'Kas perusahaan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '1002',
                'name' => 'Bank',
                'remarks' => 'Saldo rekening bank',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '1101',
                'name' => 'Piutang Usaha',
                'remarks' => 'Piutang dari customer',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '1201',
                'name' => 'Persediaan Bahan Baku',
                'remarks' => 'Stok resin dan bahan baku',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '1202',
                'name' => 'Persediaan Barang Jadi',
                'remarks' => 'Stok produk jadi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '1301',
                'name' => 'Aset Tetap',
                'remarks' => 'Nilai perolehan aset tetap',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // LIABILITIES (2xxx)
            // ======================
            [
                'code' => '2001',
                'name' => 'Hutang Usaha',
                'remarks' => 'Hutang kepada supplier',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '2101',
                'name' => 'Hutang Pajak',
                'remarks' => 'PPN dan pajak terutang',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // EQUITY (3xxx)
            // ======================
            [
                'code' => '3001',
                'name' => 'Modal Disetor',
                'remarks' => 'Modal awal pemilik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '3101',
                'name' => 'Laba Ditahan',
                'remarks' => 'Akumulasi laba perusahaan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // REVENUE (4xxx)
            // ======================
            [
                'code' => '4001',
                'name' => 'Penjualan Produk',
                'remarks' => 'Pendapatan penjualan produk plastik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // COGS (5xxx)
            // ======================
            [
                'code' => '5001',
                'name' => 'Harga Pokok Penjualan',
                'remarks' => 'HPP barang jadi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // EXPENSES (6xxx)
            // ======================
            [
                'code' => '6001',
                'name' => 'Beban Gaji',
                'remarks' => 'Gaji dan tunjangan karyawan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '6002',
                'name' => 'Beban Listrik & Air',
                'remarks' => 'Biaya listrik dan air pabrik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '6003',
                'name' => 'Beban Penyusutan',
                'remarks' => 'Penyusutan aset tetap',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
