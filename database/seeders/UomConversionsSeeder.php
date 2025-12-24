<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UomConversionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil semua UOM ID berdasarkan kode
        $uoms = DB::table('uoms')
            ->whereIn('code', [
                'BAL',
                'PACK',
                'DOS',
                'LEMBAR',
                'ROLL',
                'KG',
                'BALL',
                'GROSS'
            ])
            ->pluck('id', 'code');

        DB::table('uom_conversions')->insert([
            [
                'from_uom_id' => $uoms['BAL'],
                'to_uom_id' => $uoms['PACK'],
                'conversion_rate' => 60,
                'remarks' => '1 BAL = 60 PACK',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['DOS'],
                'to_uom_id' => $uoms['LEMBAR'],
                'conversion_rate' => 500,
                'remarks' => '1 DOS = 500 LEMBAR',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['BAL'],
                'to_uom_id' => $uoms['ROLL'],
                'conversion_rate' => 24,
                'remarks' => '1 BAL = 24 ROLL',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['DOS'],
                'to_uom_id' => $uoms['PACK'],
                'conversion_rate' => 24,
                'remarks' => '1 DOS = 24 PACK',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['BAL'],
                'to_uom_id' => $uoms['LEMBAR'],
                'conversion_rate' => 15000,
                'remarks' => '1 BAL = 15.000 LEMBAR',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['BAL'],
                'to_uom_id' => $uoms['KG'],
                'conversion_rate' => 50,
                'remarks' => '1 BAL = 50 KG',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['BALL'],
                'to_uom_id' => $uoms['ROLL'],
                'conversion_rate' => 10,
                'remarks' => '1 BALL = 10 ROLL',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['BAL'],
                'to_uom_id' => $uoms['GROSS'],
                'conversion_rate' => 50,
                'remarks' => '1 BAL = 50 GROSS',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['DOS'],
                'to_uom_id' => $uoms['ROLL'],
                'conversion_rate' => 8,
                'remarks' => '1 DOS = 8 ROLL',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'from_uom_id' => $uoms['BALL'],
                'to_uom_id' => $uoms['LEMBAR'],
                'conversion_rate' => 4000,
                'remarks' => '1 BALL = 4.000 LEMBAR',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
