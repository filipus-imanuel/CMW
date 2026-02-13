<?php

use App\Livewire\Sales\Request\Edit;
use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\System\Setting;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Helper to set up all required records for a Sales Request edit test.
 *
 * @return array{user: User, order: OrderHeader, item: Item, detail: OrderDetail, categoryPrice: CategoryPrice}
 */
function setupSalesRequestFixtures(): array
{
    $currency = Currency::create([
        'code' => 'IDR',
        'name' => 'Rupiah',
        'symbol' => 'Rp',
        'is_active' => true,
    ]);

    $uom = Uom::create([
        'code' => 'PCS',
        'name' => 'Pieces',
        'is_active' => true,
    ]);

    $itemCategory = ItemCategory::create([
        'code' => 'CAT01',
        'name' => 'Test Category',
        'is_active' => true,
    ]);

    $company = Company::create([
        'code' => 'COMP01',
        'name' => 'Test Company',
        'currency_id' => $currency->id,
        'is_active' => true,
    ]);

    $categoryPrice = CategoryPrice::create([
        'code' => 'GEN',
        'name' => 'General',
        'is_active' => true,
    ]);

    $partner = Partner::create([
        'code' => 'CUST01',
        'name' => 'Test Customer',
        'is_customer' => true,
        'is_supplier' => false,
        'category_price_id' => $categoryPrice->id,
        'is_active' => true,
    ]);

    $item = Item::create([
        'code' => 'ITEM01',
        'name' => 'Test Item',
        'type' => 'FG',
        'item_category_id' => $itemCategory->id,
        'uom_id' => $uom->id,
        'currency_id' => $currency->id,
        'cost_price' => 50.00,
        'sell_price' => 100.00, // HET = 100
        'min_stock' => 0,
        'max_stock' => 0,
        'is_active' => true,
    ]);

    ItemPrice::create([
        'item_id' => $item->id,
        'category_price_id' => $categoryPrice->id,
        'price' => 90.00, // Resolved price for GEN = 90
        'is_active' => true,
    ]);

    // Floor percentage setting (80% of HET = floor 80)
    Setting::updateOrCreate(
        ['key' => 'sales.request.floor_percentage_of_het'],
        [
            'value' => '80.00',
            'data_type' => 'decimal',
            'name_id' => 'Batas Bawah Harga (% dari HET)',
            'name_en' => 'Price Floor (% of HET)',
            'name_ch' => '价格下限',
            'category' => 'Sales',
        ]
    );

    $user = User::factory()->withoutTwoFactor()->create();

    $order = OrderHeader::create([
        'code' => 'SR/TEST/0001',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $currency->id,
        'partner_id' => $partner->id,
        'company_id' => $company->id,
        'item_category_id' => $itemCategory->id,
        'tax_mode' => 'NONE',
        'tax_rate' => 0,
        'status' => 'INIT',
        'subtotal' => 90.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 90.00,
        'created_by' => $user->id,
    ]);

    $detail = OrderDetail::create([
        'order_header_id' => $order->id,
        'item_id' => $item->id,
        'uom_id' => $uom->id,
        'quantity' => 1,
        'price' => 90.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 90.00,
        'created_by' => $user->id,
    ]);

    return compact('user', 'order', 'item', 'detail', 'categoryPrice');
}

/**
 * Give a user specific permissions by name.
 */
function grantPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'test-role', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user->assignRole($role);
}

// ──────────────────────────────────────────────────────────────────────────────
// TESTS
// ──────────────────────────────────────────────────────────────────────────────

it('allows saving without override permission when price is unchanged', function () {
    $fixtures = setupSalesRequestFixtures();
    grantPermissions($fixtures['user'], ['edit sales request']);

    Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $fixtures['order']->id])
        ->assertSet('items.0.price', '90.00')
        ->call('save')
        ->assertHasNoErrors();
});

it('blocks saving with overridden price when user lacks override permission', function () {
    $fixtures = setupSalesRequestFixtures();
    grantPermissions($fixtures['user'], ['edit sales request']);

    Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $fixtures['order']->id])
        ->set('items.0.price', '75.00') // Override from 90 → 75
        ->call('save')
        ->assertForbidden();
});

it('allows saving with overridden price when user has override permission', function () {
    $fixtures = setupSalesRequestFixtures();
    grantPermissions($fixtures['user'], ['edit sales request', 'override price sales request']);

    Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $fixtures['order']->id])
        ->set('items.0.price', '75.00') // Override from 90 → 75
        ->call('save')
        ->assertHasNoErrors();

    $fixtures['detail']->refresh();
    expect((float) $fixtures['detail']->price)->toBe(75.00);
});

it('computes guardrail warnings for price above HET', function () {
    $fixtures = setupSalesRequestFixtures();
    grantPermissions($fixtures['user'], ['edit sales request', 'override price sales request']);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $fixtures['order']->id]);

    // HET is 100, current price is 90 → no warnings
    $guardrails = $component->get('priceGuardrails');
    expect($guardrails[0]['het_price'])->toBe(100.00);
    expect($guardrails[0]['floor_price'])->toBe(80.00);
    expect($guardrails[0]['warnings'])->toBe([]);

    // Change price above HET
    $component->set('items.0.price', '110.00');
    $guardrails = $component->get('priceGuardrails');
    expect($guardrails[0]['warnings'])->toContain('above_het');
});

it('computes guardrail warnings for price below floor', function () {
    $fixtures = setupSalesRequestFixtures();
    grantPermissions($fixtures['user'], ['edit sales request', 'override price sales request']);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $fixtures['order']->id]);

    // Change price below floor (floor = 80)
    $component->set('items.0.price', '70.00');
    $guardrails = $component->get('priceGuardrails');
    expect($guardrails[0]['warnings'])->toContain('below_floor');
});

it('builds guardrail for newly added item via addItem', function () {
    $fixtures = setupSalesRequestFixtures();
    grantPermissions($fixtures['user'], ['edit sales request']);

    // Create a second item
    $item2 = Item::create([
        'code' => 'ITEM02',
        'name' => 'Second Item',
        'type' => 'FG',
        'item_category_id' => $fixtures['item']->item_category_id,
        'uom_id' => $fixtures['item']->uom_id,
        'currency_id' => $fixtures['item']->currency_id,
        'cost_price' => 30.00,
        'sell_price' => 200.00, // HET = 200
        'is_active' => true,
    ]);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $fixtures['order']->id]);

    // Simulate adding item via event
    $component->call('addItem',
        itemId: $item2->id,
        itemCode: $item2->code,
        itemName: $item2->name,
        uomId: $item2->uom_id,
        uomName: 'Pieces',
        sellPrice: 180.00,
        hetPrice: 200.00
    );

    $guardrails = $component->get('priceGuardrails');
    expect($guardrails)->toHaveCount(2);
    expect($guardrails[1]['het_price'])->toBe(200.00);
    expect($guardrails[1]['floor_price'])->toBe(160.00); // 80% of 200
    expect($guardrails[1]['resolved_price'])->toBe(180.00);
});
