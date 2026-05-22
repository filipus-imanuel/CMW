<?php

use App\Livewire\Production\Edit;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @return array{user: User, order: OrderHeader}
 */
function setupProductionEditFixtures(string $status = 'ORDER'): array
{
    $currency = Currency::create([
        'code' => 'IDR',
        'name' => 'Rupiah',
        'symbol' => 'Rp',
        'is_active' => true,
    ]);

    $company = Company::create([
        'code' => 'COMP01',
        'name' => 'Test Company',
        'currency_id' => $currency->id,
        'is_active' => true,
    ]);

    $partner = Partner::create([
        'code' => 'CUST01',
        'name' => 'Test Customer',
        'is_customer' => true,
        'is_supplier' => false,
        'is_active' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $order = OrderHeader::create([
        'code' => 'SO/TEST/0001',
        'code_request' => 'SR/TEST/0001',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $currency->id,
        'partner_id' => $partner->id,
        'company_id' => $company->id,
        'tax_mode' => 'NONE',
        'tax_rate' => 0,
        'status' => $status,
        'subtotal' => 0,
        'discount' => 0,
        'tax' => 0,
        'total' => 0,
        'production_status' => OrderHeader::PRODUCTION_ONGOING,
        'created_by' => $user->id,
    ]);

    return compact('user', 'order');
}

function grantProductionPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'production-test-role', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user->assignRole($role);
}

it('blocks editing production status without permission', function () {
    $f = setupProductionEditFixtures('ORDER');

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->assertForbidden();
});

it('allows authorized user to update production status to finish', function () {
    $f = setupProductionEditFixtures('ORDER');
    grantProductionPermissions($f['user'], ['view production order', 'edit production order']);

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->set('production_status', OrderHeader::PRODUCTION_FINISH)
        ->call('save')
        ->assertHasNoErrors();

    $f['order']->refresh();
    expect($f['order']->production_status)->toBe(OrderHeader::PRODUCTION_FINISH);
    expect($f['order']->updated_by)->toBe($f['user']->id);
});

it('rejects invalid production status values', function () {
    $f = setupProductionEditFixtures('ORDER');
    grantProductionPermissions($f['user'], ['view production order', 'edit production order']);

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->set('production_status', 'invalid_value')
        ->call('save')
        ->assertHasErrors(['production_status']);
});
