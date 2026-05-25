<?php

use App\Livewire\Production\Edit;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Warehouse;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\StockAdjustmentHeader;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @return array{user: User, order: OrderHeader, adjustment: StockAdjustmentHeader}
 */
function setupProductionEditFixtures(string $status = 'ORDER', string $productionStatus = OrderHeader::PRODUCTION_ONGOING): array
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

    $warehouse = Warehouse::create([
        'code' => 'WH01',
        'name' => 'Main Warehouse',
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
        'production_status' => $productionStatus,
        'created_by' => $user->id,
    ]);

    $adjustment = StockAdjustmentHeader::create([
        'code' => 'ADJ/TEST/001',
        'date' => now()->format('Y-m-d'),
        'warehouse_id' => $warehouse->id,
        'order_header_id' => $order->id,
        'status' => StockAdjustmentHeader::STATUS_DRAFT,
        'created_by' => $user->id,
    ]);

    return compact('user', 'order', 'adjustment');
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

it('cascades work_order_manual and production_date to linked stock adjustments when saved', function () {
    $f = setupProductionEditFixtures('ORDER');
    grantProductionPermissions($f['user'], ['edit production order']);

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->set('work_order_manual', 'WO-12345')
        ->set('production_date', '2026-06-01')
        ->call('save')
        ->assertHasNoErrors();

    $f['order']->refresh();
    $f['adjustment']->refresh();

    expect($f['order']->work_order_manual)->toBe('WO-12345');
    expect($f['order']->production_date?->format('Y-m-d'))->toBe('2026-06-01');
    expect($f['adjustment']->work_order_manual)->toBe('WO-12345');
    expect($f['adjustment']->production_date?->format('Y-m-d'))->toBe('2026-06-01');
    expect($f['adjustment']->updated_by)->toBe($f['user']->id);
});

it('allows clearing production_date on the order and linked adjustments', function () {
    $f = setupProductionEditFixtures('ORDER');
    grantProductionPermissions($f['user'], ['edit production order']);

    $f['order']->update(['production_date' => '2026-06-01']);
    $f['adjustment']->update(['production_date' => '2026-06-01']);

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->set('production_date', null)
        ->call('save')
        ->assertHasNoErrors();

    $f['order']->refresh();
    $f['adjustment']->refresh();

    expect($f['order']->production_date)->toBeNull();
    expect($f['adjustment']->production_date)->toBeNull();
});

it('redirects away when SO status is not ORDER or DELIVERY', function (string $status) {
    $f = setupProductionEditFixtures($status);
    grantProductionPermissions($f['user'], ['edit production order']);

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->assertRedirect(route('production.index'));
})->with(['INIT', 'FINISH', 'CANCELLED', 'REJECTED']);

it('locks the form and refuses to save when production_status is finish', function () {
    $f = setupProductionEditFixtures('ORDER', OrderHeader::PRODUCTION_FINISH);
    grantProductionPermissions($f['user'], ['edit production order']);

    Livewire::actingAs($f['user'])
        ->test(Edit::class, ['id' => $f['order']->id])
        ->assertSet('readOnly', true)
        ->set('work_order_manual', 'WO-LOCKED')
        ->call('save');

    $f['order']->refresh();
    $f['adjustment']->refresh();

    expect($f['order']->work_order_manual)->toBeNull();
    expect($f['adjustment']->work_order_manual)->toBeNull();
});
