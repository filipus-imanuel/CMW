<?php

use App\Helpers\CMW\CustomerCheckHelper;
use App\Livewire\Sales\Request\Edit;
use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemPrice;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Setup fixtures for customer debt tests.
 *
 * @return array{user: User, partner: Partner, company: Company, itemCategory: ItemCategory, currency: Currency, uom: Uom, item: Item, categoryPrice: CategoryPrice}
 */
function setupDebtFixtures(): array
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
    $company->itemCategories()->attach($itemCategory->id);

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
        'credit_limit' => 1000.00,
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
        'sell_price' => 100.00,
        'is_active' => true,
    ]);

    ItemPrice::create([
        'item_id' => $item->id,
        'category_price_id' => $categoryPrice->id,
        'price' => 90.00,
        'is_active' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    return compact('user', 'partner', 'company', 'itemCategory', 'currency', 'uom', 'item', 'categoryPrice');
}

/**
 * Give a user specific permissions.
 */
function grantDebtPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'debt-test-role', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user->assignRole($role);
}

// ──────────────────────────────────────────────────────────────────────────────
// CustomerCheckHelper unit-level tests
// ──────────────────────────────────────────────────────────────────────────────

it('calculates outstanding balance from unpaid AR invoices', function () {
    $fixtures = setupDebtFixtures();

    // Create unpaid invoices
    ArInvoiceHeader::create([
        'code' => 'INV/001',
        'date' => now(),
        'due_date' => now()->addDays(30),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'total' => 300.00,
        'paid' => 100.00,
        'balance' => 200.00,
        'status' => 'partial',
    ]);

    ArInvoiceHeader::create([
        'code' => 'INV/002',
        'date' => now(),
        'due_date' => now()->addDays(30),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'total' => 500.00,
        'paid' => 0,
        'balance' => 500.00,
        'status' => 'unpaid',
    ]);

    $outstanding = CustomerCheckHelper::getOutstandingBalance($fixtures['partner']->id);
    expect($outstanding)->toBe(700.00);
});

it('calculates pending orders total excluding a specific order', function () {
    $fixtures = setupDebtFixtures();

    $order1 = OrderHeader::create([
        'code' => 'SR/001',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'REQUEST',
        'total' => 200.00,
        'created_by' => $fixtures['user']->id,
    ]);

    $order2 = OrderHeader::create([
        'code' => 'SR/002',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'ORDER',
        'total' => 300.00,
        'created_by' => $fixtures['user']->id,
    ]);

    // Without exclude → both counted
    $total = CustomerCheckHelper::getPendingOrdersTotal($fixtures['partner']->id);
    expect($total)->toBe(500.00);

    // Excluding order1 → only order2 counted
    $total = CustomerCheckHelper::getPendingOrdersTotal($fixtures['partner']->id, $order1->id);
    expect($total)->toBe(300.00);
});

it('ignores INIT and FINISH/FINAL orders in pending total', function () {
    $fixtures = setupDebtFixtures();

    OrderHeader::create([
        'code' => 'SR/INIT',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'INIT',
        'total' => 999.00,
        'created_by' => $fixtures['user']->id,
    ]);

    OrderHeader::create([
        'code' => 'SR/FINAL',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'FINAL',
        'total' => 888.00,
        'created_by' => $fixtures['user']->id,
    ]);

    $total = CustomerCheckHelper::getPendingOrdersTotal($fixtures['partner']->id);
    expect($total)->toBe(0.00);
});

it('calculates projected exposure including AR + pending orders + current order', function () {
    $fixtures = setupDebtFixtures();

    // AR invoice: balance 200
    ArInvoiceHeader::create([
        'code' => 'INV/001',
        'date' => now(),
        'due_date' => now()->addDays(30),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'total' => 200.00,
        'paid' => 0,
        'balance' => 200.00,
        'status' => 'unpaid',
    ]);

    // Pending order: 300
    OrderHeader::create([
        'code' => 'SR/PENDING',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'ORDER',
        'total' => 300.00,
        'created_by' => $fixtures['user']->id,
    ]);

    // Current order total: 600
    $debt = CustomerCheckHelper::hasExcessiveDebt($fixtures['partner']->id, 600.00);

    // Projected = 200 (AR) + 300 (pending) + 600 (current) = 1100
    expect($debt['outstanding'])->toBe(200.00);
    expect($debt['pending_orders'])->toBe(300.00);
    expect($debt['current_order'])->toBe(600.00);
    expect($debt['projected'])->toBe(1100.00);
    expect($debt['limit'])->toBe(1000.00);
    expect($debt['exceeded'])->toBeTrue();
    expect($debt['remaining'])->toBe(-100.00);
});

it('returns remaining as null when credit limit is zero (unlimited)', function () {
    $fixtures = setupDebtFixtures();
    $fixtures['partner']->update(['credit_limit' => 0]);

    $debt = CustomerCheckHelper::hasExcessiveDebt($fixtures['partner']->id, 99999.00);

    expect($debt['exceeded'])->toBeFalse();
    expect($debt['remaining'])->toBeNull();
});

it('excludes the current order from pending when using excludeOrderId', function () {
    $fixtures = setupDebtFixtures();

    // Create the "current" order being edited (INIT status, so won't be in pending anyway)
    $currentOrder = OrderHeader::create([
        'code' => 'SR/CURRENT',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'INIT',
        'total' => 400.00,
        'created_by' => $fixtures['user']->id,
    ]);

    // Another order in APPROVAL status (should be counted as pending)
    OrderHeader::create([
        'code' => 'SR/OTHER',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'APPROVAL',
        'total' => 500.00,
        'created_by' => $fixtures['user']->id,
    ]);

    // runAllChecks with excludeOrderId and currentOrderTotal
    $checks = CustomerCheckHelper::runAllChecks(
        $fixtures['partner']->id,
        $fixtures['company']->id,
        $fixtures['itemCategory']->id,
        400.00,              // current order total
        $currentOrder->id    // exclude from pending
    );

    // pending_orders = 500 (only the APPROVAL order)
    expect($checks['debt']['pending_orders'])->toBe(500.00);
    expect($checks['debt']['current_order'])->toBe(400.00);
    // projected = 0 (AR) + 500 (pending) + 400 (current) = 900
    expect($checks['debt']['projected'])->toBe(900.00);
    expect($checks['debt']['exceeded'])->toBeFalse(); // 900 < 1000
});

// ──────────────────────────────────────────────────────────────────────────────
// Livewire component integration test
// ──────────────────────────────────────────────────────────────────────────────

it('shows debt checks with projected exposure in Edit component', function () {
    $fixtures = setupDebtFixtures();
    grantDebtPermissions($fixtures['user'], ['edit sales request']);

    // Create AR invoice: balance 800
    ArInvoiceHeader::create([
        'code' => 'INV/001',
        'date' => now(),
        'due_date' => now()->addDays(30),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'total' => 800.00,
        'paid' => 0,
        'balance' => 800.00,
        'status' => 'unpaid',
    ]);

    // Create the order being edited (INIT, total 90)
    $order = OrderHeader::create([
        'code' => 'SR/TEST/0001',
        'date' => now()->format('Y-m-d'),
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'currency_id' => $fixtures['currency']->id,
        'status' => 'INIT',
        'total' => 90.00,
        'created_by' => $fixtures['user']->id,
    ]);

    OrderDetail::create([
        'order_header_id' => $order->id,
        'item_id' => $fixtures['item']->id,
        'uom_id' => $fixtures['uom']->id,
        'quantity' => 1,
        'price' => 90.00,
        'created_by' => $fixtures['user']->id,
    ]);

    // Another pending order: 300
    OrderHeader::create([
        'code' => 'SR/OTHER',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'status' => 'REQUEST',
        'total' => 300.00,
        'created_by' => $fixtures['user']->id,
    ]);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Edit::class, ['id' => $order->id]);

    $checks = $component->get('checks');

    // AR = 800, pending orders = 300 (excludes current INIT order), current = 90
    expect($checks['debt']['outstanding'])->toBe(800.00);
    expect($checks['debt']['pending_orders'])->toBe(300.00);
    expect($checks['debt']['current_order'])->toBe(90.00);
    // Projected = 800 + 300 + 90 = 1190 > 1000 limit
    expect($checks['debt']['projected'])->toBe(1190.00);
    expect($checks['debt']['exceeded'])->toBeTrue();
    expect($checks['debt']['remaining'])->toBe(-190.00);
});
