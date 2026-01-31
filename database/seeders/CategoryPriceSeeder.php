<?php

namespace Database\Seeders;

use App\Models\CMW\Inventory\CategoryPrice;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class CategoryPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'GEN',
                'name' => 'General',
                'remarks' => 'Default pricing category for all customers',
            ],
            [
                'code' => 'VIP',
                'name' => 'VIP',
                'remarks' => 'VIP customer pricing category with special rates',
            ],
        ];

        foreach ($data as $item) {
            try {
                CategoryPrice::updateOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'remarks' => $item['remarks'],
                        'is_active' => true,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]
                );
            } catch (QueryException $e) {
                $this->command->error("Failed to seed category price: {$item['code']} - {$e->getMessage()}");
            } catch (Exception $e) {
                $this->command->error("Unexpected error seeding category price: {$item['code']} - {$e->getMessage()}");
            }
        }
    }
}
