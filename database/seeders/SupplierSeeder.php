<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('partners')->insert([
            [
                'code' => 'SUP-001',
                'name' => 'PT Polychem Indonesia',
                'is_supplier' => true,
                'is_customer' => false,
                'remarks' => 'Supplier biji plastik PP & PE lokal',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'Thai Plastic Resin Co., Ltd',
                'is_supplier' => true,
                'is_customer' => false,
                'remarks' => 'Supplier resin import (Thailand)',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'PT Chandra Asri Petrochemical',
                'is_supplier' => true,
                'is_customer' => false,
                'remarks' => 'Supplier resin PE & PP domestik',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $partners = DB::table('partners')
            ->whereIn('code', ['SUP-001', 'SUP-002', 'SUP-003'])
            ->pluck('id', 'code');

        DB::table('partner_addresses')->insert([
            [
                'partner_id' => $partners['SUP-001'],
                'label' => 'Head Office',
                'address' => 'Jl. Industri Raya No. 15, Kawasan Industri Pulogadung',
                'city' => 'Jakarta Timur',
                'phone' => '+62 21 460 8899',
                'contact_person' => 'Purchasing Dept.',
                'remarks' => 'Alamat utama penagihan & pengiriman',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'partner_id' => $partners['SUP-002'],
                'label' => 'Overseas Office',
                'address' => '99 Sukhumvit Road, Klongtoey, Wattana',
                'city' => 'Bangkok',
                'phone' => '+66 2 123 4567',
                'contact_person' => 'Export Sales Team',
                'remarks' => 'Supplier resin import Thailand',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'partner_id' => $partners['SUP-003'],
                'label' => 'Head Office',
                'address' => 'Wisma Barito Pacific Tower A, Jl. Letjen S. Parman Kav. 62-63',
                'city' => 'Jakarta Barat',
                'phone' => '+62 21 530 7950',
                'contact_person' => 'Sales Dept.',
                'remarks' => 'Kantor pusat Chandra Asri',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
