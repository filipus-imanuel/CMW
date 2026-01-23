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

        // Permissions and Roles must be seeded early (before UserSeeder for role assignment)
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);

        // User must be seeded before other tables (created_by FK dependency)
        $this->call(UserSeeder::class);

        // Currency must be seeded before Company (FK dependency)
        $this->call(CurrencySeeder::class);

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
