<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('companies')->insert([
            [
                'code' => 'COM-001',
                'name' => 'Company 1',
                'remarks' => 'Initial Company 1',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'COM-002',
                'name' => 'Company 2',
                'remarks' => 'Initial Company 2',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'COM-003',
                'name' => 'Company 3',
                'remarks' => 'Initial Company 3',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'COM-004',
                'name' => 'Company 4',
                'remarks' => 'Initial Company 4',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'COM-005',
                'name' => 'Company 5',
                'remarks' => 'Initial Company 5',
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
