<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PartnerAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil partner ID berdasarkan code (aman untuk ERP)
        $partners = DB::table('partners')
            ->whereIn('code', [
                'SUP-RESIN-001',
                'SUP-IMPORT-002',
                'CUS-IND-001',
                'CUS-TRD-002',
                'BOTH-001',
            ])
            ->pluck('id', 'code');

        DB::table('partner_addresses')->insert([
            // ======================
            // SUP-RESIN-001
            // ======================
            [
                'partner_id' => $partners['SUP-RESIN-001'],
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

            // ======================
            // SUP-IMPORT-002
            // ======================
            [
                'partner_id' => $partners['SUP-IMPORT-002'],
                'label' => 'Overseas Office',
                'address' => '99 ถนนสุขุมวิท แขวงคลองเตย แขวงคลองตัน เขตวัฒนา',
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

            // ======================
            // CUS-IND-001
            // ======================
            [
                'partner_id' => $partners['CUS-IND-001'],
                'label' => 'Factory',
                'address' => 'Jl. Raya Cikarang No. 88, Kawasan Industri Jababeka',
                'city' => 'Bekasi',
                'phone' => '+62 21 898 7766',
                'contact_person' => 'Warehouse Supervisor',
                'remarks' => 'Alamat pengiriman utama',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // CUS-TRD-002
            // ======================
            [
                'partner_id' => $partners['CUS-TRD-002'],
                'label' => 'Office & Warehouse',
                'address' => 'Jl. Margomulyo Indah Blok H No. 12',
                'city' => 'Surabaya',
                'phone' => '+62 31 749 3322',
                'contact_person' => 'Sales Admin',
                'remarks' => 'Alamat kantor dan gudang',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ======================
            // BOTH-001 (Multi Address)
            // ======================
            [
                'partner_id' => $partners['BOTH-001'],
                'label' => 'Head Office',
                'address' => 'Jl. Gatot Subroto Kav. 22',
                'city' => 'Jakarta Selatan',
                'phone' => '+62 21 525 8899',
                'contact_person' => 'Finance Dept.',
                'remarks' => 'Alamat penagihan',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'partner_id' => $partners['BOTH-001'],
                'label' => 'Warehouse',
                'address' => 'Jl. Raya Karawang Km 45, Kawasan Industri KIIC',
                'city' => 'Karawang',
                'phone' => '+62 21 8910 2233',
                'contact_person' => 'Logistic Manager',
                'remarks' => 'Alamat pengiriman barang',
                'is_default' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
