<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountCvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('account_cvs')->insert([
            [
                'code' => 'ACV-001',
                'name' => 'Account CV 1',
                'remarks' => 'Initial Account CV 1',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ACV-002',
                'name' => 'Account CV 2',
                'remarks' => 'Initial Account CV 2',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ACV-003',
                'name' => 'Account CV 3',
                'remarks' => 'Initial Account CV 3',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ACV-004',
                'name' => 'Account CV 4',
                'remarks' => 'Initial Account CV 4',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ACV-005',
                'name' => 'Account CV 5',
                'remarks' => 'Initial Account CV 5',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
