<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CoaOpeningBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $period = '2025-01';

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
                '3101', // Laba Ditahan
            ])
            ->pluck('id', 'code');

        DB::table('coa_opening_balances')->insert([
            // ======================
            // ASSETS (DEBIT)
            // ======================
            [
                'coa_id' => $coas['1001'],
                'period' => $period,
                'debit' => 150000000.00,
                'credit' => 0,
                'remarks' => 'Saldo awal kas',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['1002'],
                'period' => $period,
                'debit' => 500000000.00,
                'credit' => 0,
                'remarks' => 'Saldo awal bank',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['1101'],
                'period' => $period,
                'debit' => 250000000.00,
                'credit' => 0,
                'remarks' => 'Saldo awal piutang usaha',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['1201'],
                'period' => $period,
                'debit' => 300000000.00,
                'credit' => 0,
                'remarks' => 'Saldo awal persediaan bahan baku',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['1202'],
                'period' => $period,
                'debit' => 200000000.00,
                'credit' => 0,
                'remarks' => 'Saldo awal persediaan barang jadi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['1301'],
                'period' => $period,
                'debit' => 950000000.00,
                'credit' => 0,
                'remarks' => 'Saldo awal aset tetap',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // LIABILITIES (CREDIT)
            // ======================
            [
                'coa_id' => $coas['2001'],
                'period' => $period,
                'debit' => 0,
                'credit' => 220000000.00,
                'remarks' => 'Saldo awal hutang usaha',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['2101'],
                'period' => $period,
                'debit' => 0,
                'credit' => 80000000.00,
                'remarks' => 'Saldo awal hutang pajak',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // EQUITY (CREDIT)
            // ======================
            [
                'coa_id' => $coas['3001'],
                'period' => $period,
                'debit' => 0,
                'credit' => 1550000000.00,
                'remarks' => 'Modal disetor pemilik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'coa_id' => $coas['3101'],
                'period' => $period,
                'debit' => 0,
                'credit' => 475000000.00,
                'remarks' => 'Laba ditahan periode sebelumnya',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
