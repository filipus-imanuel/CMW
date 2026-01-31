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
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
