<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Partner;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categoryPrices = CategoryPrice::pluck('id', 'code');

        DB::table('partners')->insert([
            [
                'code' => 'CUS-001',
                'name' => 'PT Maju Jaya Packaging',
                'is_supplier' => false,
                'is_customer' => true,
                'category_price_id' => $categoryPrices['GEN'],
                'remarks' => 'Customer industri kemasan makanan',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CUS-002',
                'name' => 'CV Sinar Plastik',
                'is_supplier' => false,
                'is_customer' => true,
                'category_price_id' => $categoryPrices['VIP'],
                'remarks' => 'Customer trading plastik, VIP pricing',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CUS-003',
                'name' => 'PT Berkah Mandiri',
                'is_supplier' => false,
                'is_customer' => true,
                'category_price_id' => $categoryPrices['GEN'],
                'remarks' => 'Customer distributor sedotan & cup',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CUS-004',
                'name' => 'PT Sentosa Plastindo',
                'is_supplier' => false,
                'is_customer' => true,
                'category_price_id' => $categoryPrices['VIP'],
                'remarks' => 'Customer kantong plastik, VIP pricing',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CUS-005',
                'name' => 'PT Global Polymer',
                'is_supplier' => true,
                'is_customer' => true,
                'category_price_id' => $categoryPrices['GEN'],
                'remarks' => 'Partner dua arah (jual & beli resin)',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $companyCustomers = [
            'COM-001' => ['CUS-001', 'CUS-002', 'CUS-005'],
            'COM-002' => ['CUS-003', 'CUS-004'],
            'COM-003' => ['CUS-001', 'CUS-003', 'CUS-005'],
        ];

        foreach ($companyCustomers as $companyCode => $customerCodes) {
            $company = Company::where('code', $companyCode)->first();
            $partnerIds = Partner::whereIn('code', $customerCodes)->pluck('id');
            $company->partners()->syncWithoutDetaching($partnerIds);
        }

        $partners = Partner::whereIn('code', ['CUS-001', 'CUS-002', 'CUS-003', 'CUS-004', 'CUS-005'])
            ->pluck('id', 'code');

        DB::table('partner_addresses')->insert([
            [
                'partner_id' => $partners['CUS-001'],
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
            [
                'partner_id' => $partners['CUS-002'],
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
            [
                'partner_id' => $partners['CUS-003'],
                'label' => 'Head Office',
                'address' => 'Jl. Raya Serpong Km 7 No. 32',
                'city' => 'Tangerang Selatan',
                'phone' => '+62 21 538 1100',
                'contact_person' => 'Purchasing',
                'remarks' => 'Alamat utama pengiriman',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'partner_id' => $partners['CUS-004'],
                'label' => 'Factory',
                'address' => 'Jl. Raya Serang Km 24, Kawasan Industri Cikupa',
                'city' => 'Tangerang',
                'phone' => '+62 21 596 1234',
                'contact_person' => 'Gudang Dept.',
                'remarks' => 'Alamat pabrik dan gudang',
                'is_default' => true,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'partner_id' => $partners['CUS-005'],
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
                'partner_id' => $partners['CUS-005'],
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
