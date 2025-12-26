<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(UserSeeder::class);

        $this->call(PartnersSeeder::class);
        $this->call(PartnerAddressesSeeder::class);
        $this->call(CreditTermsSeeder::class);
        $this->call(UomsSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ItemsSeeder::class);
        $this->call(PositionsSeeder::class);
        $this->call(EmployeesSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(CompanySettingSeeder::class);
        $this->call(UomConversionsSeeder::class);
        $this->call(WarehousesSeeder::class);
        $this->call(TaxesSeeder::class);
        $this->call(AssetsSeeder::class);
        $this->call(CoasSeeder::class);
        $this->call(CoaOpeningBalancesSeeder::class);
        $this->call(CoaSettingsSeeder::class);
        $this->call(UserGroupsSeeder::class);
        $this->call(MenusSeeder::class);
    }
}
