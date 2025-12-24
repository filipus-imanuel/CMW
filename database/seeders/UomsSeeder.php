<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('uoms')->insert([
            [
                'code' => 'BAL',
                'name' => 'BAL',
                'remarks' => 'Satuan bal',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BALL',
                'name' => 'BALL',
                'remarks' => 'Satuan ball (tidak digunakan)',
                'is_active' => false,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'COLLY',
                'name' => 'COLLY',
                'remarks' => 'Satuan colly (tidak digunakan)',
                'is_active' => false,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'DOS',
                'name' => 'DOS',
                'remarks' => 'Satuan dus',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'GROSS',
                'name' => 'GROSS',
                'remarks' => 'Satuan gross',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'KG',
                'name' => 'KG',
                'remarks' => 'Kilogram',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LEMBAR',
                'name' => 'LEMBAR',
                'remarks' => 'Satuan lembar',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'M',
                'name' => 'M',
                'remarks' => 'Meter (tidak digunakan)',
                'is_active' => false,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'M2',
                'name' => 'M2',
                'remarks' => 'Meter persegi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'METER',
                'name' => 'METER',
                'remarks' => 'Satuan meter',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PACK',
                'name' => 'PACK',
                'remarks' => 'Satuan pack',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PAK',
                'name' => 'PAK',
                'remarks' => 'Satuan pak (tidak digunakan)',
                'is_active' => false,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PCS',
                'name' => 'Pieces',
                'remarks' => 'Satuan pcs',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PIECES',
                'name' => 'PIECES',
                'remarks' => 'Satuan pieces (tidak digunakan)',
                'is_active' => false,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ROLL',
                'name' => 'ROLL',
                'remarks' => 'Satuan roll',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SET',
                'name' => 'SET',
                'remarks' => 'Satuan set',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'UNIT',
                'name' => 'UNIT',
                'remarks' => 'Satuan unit',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
