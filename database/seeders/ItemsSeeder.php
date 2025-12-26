<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil UOM ID berdasarkan kode
        $uoms = DB::table('uoms')
            ->whereIn('code', ['LEMBAR', 'ROLL', 'KG'])
            ->pluck('id', 'code');

        DB::table('items')->insert([
            [
                'code' => 'ITM-FLM-PP-001',
                'name' => 'PP Film 12 x 12 cm',
                'type' => 'finished_goods',
                'uom_id' => $uoms['LEMBAR'],
                'category_id' => 2,
                'cost_price' => 8500,
                'sell_price' => 11000,
                'min_stock' => 100,
                'max_stock' => 5000,
                'remarks' => 'Produk potong PP film ukuran 12 x 12 cm',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ITM-FLM-PP-002',
                'name' => 'PP Film 12 x 13 cm',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['ROLL'],
                'cost_price' => 420000,
                'sell_price' => 480000,
                'min_stock' => 10,
                'max_stock' => 200,
                'remarks' => 'PP film dalam bentuk roll',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ITM-STRETCH-001',
                'name' => 'Stretch Film Industri',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['KG'],
                'cost_price' => 18500,
                'sell_price' => 23000,
                'min_stock' => 200,
                'max_stock' => 10000,
                'remarks' => 'Stretch film untuk kebutuhan industri',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ITM-WRAP-001',
                'name' => 'Plastic Wrap Lembaran',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['LEMBAR'],
                'cost_price' => 3000,
                'sell_price' => 4500,
                'min_stock' => 0,
                'max_stock' => 0,
                'remarks' => 'Produk wrap lama (tidak aktif)',
                'is_active' => false,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ITM-KLIP-STD-001',
                'name' => 'Klip Plastik Standar',
                'type' => 'finished_goods',
                'category_id' => 2,
                'uom_id' => $uoms['ROLL'],
                'cost_price' => 150000,
                'sell_price' => 195000,
                'min_stock' => 20,
                'max_stock' => 1000,
                'remarks' => 'Klip plastik untuk kemasan',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
