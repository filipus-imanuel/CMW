<?php

namespace App\Providers;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Country;
use App\Models\CMW\Master\CreditTerm;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Department;
use App\Models\CMW\Master\Employee;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\UserGroup;
use App\Models\CMW\Master\Warehouse;
use App\Observers\InventoryModelObserver;
use App\Observers\MasterModelObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register MasterModelObserver for all master models that use PopulateDataHelper
        $masterModels = [
            Company::class,
            Country::class,
            CreditTerm::class,
            Currency::class,
            Department::class,
            Employee::class,
            Partner::class,
            Tax::class,
            Uom::class,
            UserGroup::class,
            Warehouse::class,
        ];

        foreach ($masterModels as $model) {
            $model::observe(MasterModelObserver::class);
        }

        // Register InventoryModelObserver for all inventory models that use PopulateDataHelper
        $inventoryModels = [
            CategoryPrice::class,
            Item::class,
            ItemCategory::class,
            ItemPrice::class,
        ];

        foreach ($inventoryModels as $model) {
            $model::observe(InventoryModelObserver::class);
        }
    }
}
