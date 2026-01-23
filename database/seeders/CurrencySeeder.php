<?php

namespace Database\Seeders;

use App\Models\CMW\Master\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        Currency::insert([
            [
                'id' => 1,
                'code' => 'IDR',
                'name' => 'Rupiah',
                'symbol' => 'Rp',
                'symbol_position' => 'BEFORE',
                'rate' => 1.00000,
                'remarks' => 'Indonesian Rupiah - Base Currency',
                'is_edit_locked' => true,
                'is_delete_locked' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);
    }
}
