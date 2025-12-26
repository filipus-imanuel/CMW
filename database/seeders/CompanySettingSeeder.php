<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('company_settings')->insert([
            [
                'category_id' => 1,
                'employee_id' => 5,
                'company_id' => 1,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 2,
                'employee_id' => 5,
                'company_id' => 1,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 3,
                'employee_id' => 5,
                'company_id' => 2,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 4,
                'employee_id' => 5,
                'company_id' => 3,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 5,
                'employee_id' => 5,
                'company_id' => 4,
                'version_number' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
