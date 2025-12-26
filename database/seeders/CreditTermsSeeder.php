<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreditTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * Ambil 1 alamat default
         * (karena credit_terms butuh partner_address_id)
         */
        $defaultAddressId = DB::table('partner_addresses')
            ->where('is_default', true)
            ->where('is_active', true)
            ->value('id');

        if (! $defaultAddressId) {
            throw new \Exception('Tidak ditemukan partner address default untuk credit term seeder.');
        }

        DB::table('credit_terms')->insert([
            [
                'code' => 'T0',
                'name' => '0 Hari (CASH)',
                'partner_address_id' => $defaultAddressId,
                'days' => 0,
                'description' => 'Pembayaran tunai (Cash Before Delivery)',
                'remarks' => 'Default cash term',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T7',
                'name' => '7 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 7,
                'description' => 'Pembayaran 7 hari setelah invoice',
                'remarks' => null,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T14',
                'name' => '14 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 14,
                'description' => 'Pembayaran 14 hari setelah invoice',
                'remarks' => null,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T21',
                'name' => '21 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 21,
                'description' => 'Pembayaran 21 hari setelah invoice',
                'remarks' => null,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T30',
                'name' => '30 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 30,
                'description' => 'Pembayaran 30 hari setelah invoice',
                'remarks' => 'Net 30',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T45',
                'name' => '45 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 45,
                'description' => 'Pembayaran 45 hari setelah invoice',
                'remarks' => null,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T50',
                'name' => '50 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 50,
                'description' => 'Pembayaran 50 hari setelah invoice',
                'remarks' => null,
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T60',
                'name' => '60 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 60,
                'description' => 'Pembayaran 60 hari setelah invoice',
                'remarks' => 'Net 60',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'T90',
                'name' => '90 Hari',
                'partner_address_id' => $defaultAddressId,
                'days' => 90,
                'description' => 'Pembayaran 90 hari setelah invoice',
                'remarks' => 'Kredit khusus customer besar',
                'is_active' => true,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
