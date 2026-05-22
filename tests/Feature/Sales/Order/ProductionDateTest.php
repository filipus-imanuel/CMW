<?php

use App\Livewire\Sales\Order\Show;
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
function setupProductionDateFixtures(): array
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
        'status' => 'ORDER',
        'subtotal' => 0,
        'discount' => 0,
        'tax' => 0,
        'total' => 0,
        'production_status' => OrderHeader::PRODUCTION_ONGOING,
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

function grantOrderPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'order-test-role', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user->assignRole($role);
}

it('cascades production_date to linked stock adjustments when saved on sales order', function () {
    $f = setupProductionDateFixtures();
    grantOrderPermissions($f['user'], ['view sales order', 'edit sales order']);

    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $f['order']->id])
        ->set('work_order_manual', 'WO-12345')
        ->set('production_date', '2026-06-01')
        ->call('saveWorkOrderManual')
        ->assertHasNoErrors();

    $f['order']->refresh();
    $f['adjustment']->refresh();

    expect($f['order']->work_order_manual)->toBe('WO-12345');
    expect($f['order']->production_date?->format('Y-m-d'))->toBe('2026-06-01');
    expect($f['adjustment']->work_order_manual)->toBe('WO-12345');
    expect($f['adjustment']->production_date?->format('Y-m-d'))->toBe('2026-06-01');
    expect($f['adjustment']->updated_by)->toBe($f['user']->id);
});

it('allows clearing production_date on sales order and linked adjustments', function () {
    $f = setupProductionDateFixtures();
    grantOrderPermissions($f['user'], ['view sales order', 'edit sales order']);

    $f['order']->update(['production_date' => '2026-06-01']);
    $f['adjustment']->update(['production_date' => '2026-06-01']);

    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $f['order']->id])
        ->set('production_date', null)
        ->call('saveWorkOrderManual')
        ->assertHasNoErrors();

    $f['order']->refresh();
    $f['adjustment']->refresh();

    expect($f['order']->production_date)->toBeNull();
    expect($f['adjustment']->production_date)->toBeNull();
});
