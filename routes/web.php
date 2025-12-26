<?php

use App\Livewire\Inventories\Item\Index as ItemIndex;
use App\Livewire\Masters\Country\Index as CountryIndex;
use App\Livewire\Masters\CreditTerm\Index as CreditTermIndex;
use App\Livewire\Masters\Employee\Index as EmployeeIndex;
use App\Livewire\Masters\Position\Index as PositionIndex;
use App\Livewire\Masters\Tax\Index as TaxIndex;
use App\Livewire\Masters\Uom\Index as UomIndex;
use App\Livewire\Masters\UomConversion\Index as UomConversionIndex;
use App\Livewire\Masters\UserGroup\Index as UserGroupIndex;
use App\Livewire\Masters\Warehouse\Index as WarehouseIndex;
use App\Livewire\Partners\CustomerAddresses\Index as CustomerAddressIndex;
use App\Livewire\Partners\Customers\Index as CustomerIndex;
use App\Livewire\Partners\SupplierAddresses\Index as SupplierAddressIndex;
use App\Livewire\Partners\Suppliers\Index as SupplierIndex;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::prefix('cmw')->group(function () {
        Route::prefix('masters')->name('masters.')->group(function () {
            Route::get('countries', CountryIndex::class)->name('countries.index');
            Route::get('positions', PositionIndex::class)->name('positions.index');
            Route::get('employees', EmployeeIndex::class)->name('employees.index');
            Route::get('user-groups', UserGroupIndex::class)->name('user-groups.index');
            Route::get('uoms', UomIndex::class)->name('uoms.index');
            Route::get('uom-conversions', UomConversionIndex::class)->name('uom-conversions.index');
            Route::get('taxes', TaxIndex::class)->name('taxes.index');
            Route::get('credit-terms', CreditTermIndex::class)->name('credit-terms.index');
            Route::get('warehouses', WarehouseIndex::class)->name('warehouses.index');
        });

        Route::prefix('partners')->name('partners.')->group(function () {
            Route::get('suppliers', SupplierIndex::class)->name('suppliers.index');
            Route::get('customers', CustomerIndex::class)->name('customers.index');
            Route::get('customer-addresses', CustomerAddressIndex::class)->name('customer-addresses.index');
            Route::get('supplier-addresses', SupplierAddressIndex::class)->name('supplier-addresses.index');
        });

        Route::prefix('inventories')->name('inventories.')->group(function () {
            Route::get('items', ItemIndex::class)->name('items.index');
        });
    });
});
