<?php

use App\Livewire\Masters\Country\Index as CountryIndex;
use App\Livewire\Masters\Position\Index as PositionIndex;
use App\Livewire\Masters\Tax\Index as TaxIndex;
use App\Livewire\Masters\Uom\Index as UomIndex;
use App\Livewire\Masters\UserGroup\Index as UserGroupIndex;
use App\Livewire\Masters\Warehouse\Index as WarehouseIndex;
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
            Route::get('user-groups', UserGroupIndex::class)->name('user-groups.index');
            Route::get('uoms', UomIndex::class)->name('uoms.index');
            Route::get('taxes', TaxIndex::class)->name('taxes.index');
            Route::get('warehouses', WarehouseIndex::class)->name('warehouses.index');
        });
    });
});
