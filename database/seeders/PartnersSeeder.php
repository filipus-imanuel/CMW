<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('partners')->insert([
            // ======================
            // SUPPLIERS
            // ======================
            [
                'code' => 'SUP-RESIN-001',
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
                'code' => 'SUP-IMPORT-002',
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

            // ======================
            // CUSTOMERS
            // ======================
            [
                'code' => 'CUS-IND-001',
                'name' => 'PT Maju Jaya Packaging',
                'is_supplier' => false,
                'is_customer' => true,
                'remarks' => 'Customer industri kemasan',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CUS-TRD-002',
                'name' => 'CV Sinar Plastik',
                'is_supplier' => false,
                'is_customer' => true,
                'remarks' => 'Customer trading biji plastik',
                'is_edit_locked' => false,
                'is_delete_locked' => false,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // ======================
            // SUPPLIER + CUSTOMER
            // ======================
            [
                'code' => 'BOTH-001',
                'name' => 'PT Global Polymer',
                'is_supplier' => true,
                'is_customer' => true,
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
    }
}
