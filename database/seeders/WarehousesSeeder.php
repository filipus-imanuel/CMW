<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehousesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('warehouses')->insert([
            [
                'code' => 'WH-RM-01',
                'name' => 'Gudang Bahan Baku',
                'address' => 'Jl. Industri Raya No. 15, Kawasan Industri Pulogadung, Jakarta Timur',
                'remarks' => 'Gudang penyimpanan resin dan bahan baku biji plastik',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'WH-FG-01',
                'name' => 'Gudang Barang Jadi',
                'address' => 'Jl. Industri Raya No. 15, Kawasan Industri Pulogadung, Jakarta Timur',
                'remarks' => 'Gudang produk jadi siap jual',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'WH-SCRAP-01',
                'name' => 'Gudang Scrap & Retur',
                'address' => 'Area belakang pabrik – Pulogadung',
                'remarks' => 'Gudang barang rusak, retur, dan scrap produksi',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'WH-TRANS-01',
                'name' => 'Gudang Transit',
                'address' => 'Area loading dock utama',
                'remarks' => 'Gudang sementara untuk barang keluar/masuk',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Assign items to warehouses:
        // Finished goods (FINISHED_GOOD) → WH-FG-01
        // Raw materials (RAW_MATERIAL)   → WH-RM-01
        $whFg = Warehouse::where('code', 'WH-FG-01')->value('id');
        $whRm = Warehouse::where('code', 'WH-RM-01')->value('id');

        $itemWarehouseRows = [];
        $items = Item::select('id', 'type')->get();
        foreach ($items as $item) {
            $warehouseId = $item->type === 'RAW_MATERIAL' ? $whRm : $whFg;
            $itemWarehouseRows[] = [
                'item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('item_warehouses')->insert($itemWarehouseRows);

        // Assign FG warehouse to all companies
        $companyWarehouseRows = [];
        $companies = Company::pluck('id');
        foreach ($companies as $companyId) {
            $companyWarehouseRows[] = [
                'company_id' => $companyId,
                'warehouse_id' => $whFg,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('company_warehouses')->insert($companyWarehouseRows);
    }
}
