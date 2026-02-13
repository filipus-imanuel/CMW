<?php

use App\Livewire\Inventories\CategoryPrice\Index as CategoryPriceIndex;
use App\Livewire\Inventories\HistoryItemPrice\Index as HistoryItemPriceIndex;
use App\Livewire\Inventories\Item\Index as ItemIndex;
use App\Livewire\Inventories\ItemCategory\Index as ItemCategoryIndex;
use App\Livewire\Inventories\ItemPrice\Approval as ItemPriceApproval;
use App\Livewire\Inventories\ItemPrice\ApprovalHistory as ItemPriceApprovalHistory;
use App\Livewire\Inventories\ItemPrice\Index as ItemPriceIndex;
use App\Livewire\Masters\Company\Index as CompanyIndex;
use App\Livewire\Masters\Country\Index as CountryIndex;
use App\Livewire\Masters\CreditTerm\Index as CreditTermIndex;
use App\Livewire\Masters\Currency\Index as CurrencyIndex;
use App\Livewire\Masters\Department\Index as DepartmentIndex;
use App\Livewire\Masters\Employee\Index as EmployeeIndex;
use App\Livewire\Masters\ExchangeRate\Index as ExchangeRateIndex;
use App\Livewire\Masters\Tax\Index as TaxIndex;
use App\Livewire\Masters\Uom\Index as UomIndex;
use App\Livewire\Masters\UomConversion\Index as UomConversionIndex;
use App\Livewire\Masters\UserGroup\Index as UserGroupIndex;
use App\Livewire\Masters\Warehouse\Index as WarehouseIndex;
use App\Livewire\Partners\CustomerAddresses\Index as CustomerAddressIndex;
use App\Livewire\Partners\Customers\Index as CustomerIndex;
use App\Livewire\Partners\SupplierAddresses\Index as SupplierAddressIndex;
use App\Livewire\Partners\Suppliers\Index as SupplierIndex;
use App\Livewire\Sales\Request\Approval\Index as SalesRequestApprovalIndex;
use App\Livewire\Sales\Request\Approval\Show as SalesRequestApprovalShow;
use App\Livewire\Sales\Request\Create as SalesRequestCreate;
use App\Livewire\Sales\Request\Edit as SalesRequestEdit;
use App\Livewire\Sales\Request\Index\Init as SalesRequestInitIndex;
use App\Livewire\Sales\Request\Index\Request as SalesRequestApprovedIndex;
use App\Livewire\System\Setting\Edit as SystemSettingEdit;
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
            Route::get('companies', CompanyIndex::class)->name('companies.index');
            Route::get('countries', CountryIndex::class)->name('countries.index');
            Route::get('credit-terms', CreditTermIndex::class)->name('credit-terms.index');
            Route::get('currencies', CurrencyIndex::class)->name('currencies.index');
            Route::get('employees', EmployeeIndex::class)->name('employees.index');
            Route::get('exchange-rates', ExchangeRateIndex::class)->name('exchange-rates.index');
            Route::get('departments', DepartmentIndex::class)->name('departments.index');
            Route::get('taxes', TaxIndex::class)->name('taxes.index');
            Route::get('uom-conversions', UomConversionIndex::class)->name('uom-conversions.index');
            Route::get('uoms', UomIndex::class)->name('uoms.index');
            Route::get('user-groups', UserGroupIndex::class)->name('user-groups.index');
            Route::get('warehouses', WarehouseIndex::class)->name('warehouses.index');
        });

        Route::prefix('partners')->name('partners.')->group(function () {
            Route::get('suppliers', SupplierIndex::class)->name('suppliers.index');
            Route::get('customers', CustomerIndex::class)->name('customers.index');
            Route::get('customer-addresses', CustomerAddressIndex::class)->name('customer-addresses.index');
            Route::get('supplier-addresses', SupplierAddressIndex::class)->name('supplier-addresses.index');
        });

        Route::prefix('inventories')->name('inventories.')->group(function () {
            Route::get('category-prices', CategoryPriceIndex::class)->name('category-prices.index');
            Route::get('item-categories', ItemCategoryIndex::class)->name('item-categories.index');
            Route::get('item-price-approval-history', ItemPriceApprovalHistory::class)->name('item-price-approval-history.index');
            Route::get('item-price-approvals', ItemPriceApproval::class)->name('item-price-approvals.index');
            Route::get('item-price-history', HistoryItemPriceIndex::class)->name('item-price-history.index');
            Route::get('item-prices', ItemPriceIndex::class)->name('item-prices.index');
            Route::get('items', ItemIndex::class)->name('items.index');
        });

        Route::prefix('system')->name('system.')->group(function () {
            Route::get('settings', SystemSettingEdit::class)->name('settings.edit');
        });

        Route::prefix('sales')->name('sales.')->group(function () {
            Route::prefix('requests')->name('request.')->group(function () {
                Route::get('/', SalesRequestInitIndex::class)->name('index.init');
                Route::get('/approved', SalesRequestApprovedIndex::class)->name('index.request');
                Route::get('/create', SalesRequestCreate::class)->name('create');
                Route::get('/{id}/edit', SalesRequestEdit::class)->name('edit');
                Route::get('/approval', SalesRequestApprovalIndex::class)->name('approval.index');
                Route::get('/approval/{id}', SalesRequestApprovalShow::class)->name('approval.show');
            });
        });
    });
});
