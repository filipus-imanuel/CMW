<?php

namespace App\Providers;

use App\Models\CMW\Master\CategoryPrice;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Country;
use App\Models\CMW\Master\CreditTerm;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Employee;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Position;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\UserGroup;
use App\Models\CMW\Master\Warehouse;
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
            CategoryPrice::class,
            Company::class,
            Country::class,
            CreditTerm::class,
            Currency::class,
            Employee::class,
            Item::class,
            Partner::class,
            Position::class,
            Tax::class,
            Uom::class,
            UserGroup::class,
            Warehouse::class,
        ];

        foreach ($masterModels as $model) {
            $model::observe(MasterModelObserver::class);
        }
    }
}
