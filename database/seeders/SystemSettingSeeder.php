<?php

namespace Database\Seeders;

use App\Models\CMW\System\Setting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'inventory.item_price.threshold_bypass_approval',
                'value' => '10.00',
                'data_type' => 'decimal',
                'name_id' => 'Batas Perubahan Harga Tanpa Approval (%)',
                'name_en' => 'Price Change Threshold Without Approval (%)',
                'name_ch' => '无需审批的价格变更阈值 (%)',
                'category' => 'Inventory',
            ],
            [
                'key' => 'sales.request.floor_percentage_of_het',
                'value' => '80.00',
                'data_type' => 'decimal',
                'name_id' => 'Batas Bawah Harga (% dari HET)',
                'name_en' => 'Price Floor (% of HET)',
                'name_ch' => '价格下限 (占最高零售价的百分比)',
                'category' => 'Sales',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
