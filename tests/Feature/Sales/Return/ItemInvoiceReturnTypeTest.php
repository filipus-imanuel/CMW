<?php

use App\Livewire\Sales\Request\Create;
use App\Livewire\Sales\Request\Edit as SalesRequestEdit;
use App\Livewire\Sales\Return\Create as SalesReturnCreate;
use App\Livewire\Sales\Return\Show as SalesReturnShow;
use App\Livewire\Warehouses\Return\Show as WarehouseReturnShow;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\Warehouse;
use App\Models\CMW\Transaction\ArInvoiceHeader;
use App\Models\CMW\Transaction\DeliveryDetail;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderDetail;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\CMW\Transaction\ReturnDetail;
use App\Models\CMW\Transaction\ReturnHeader;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Create all required records for ITEM_INVOICE return type tests.
 *
 * @return array{user: User, currency: Currency, uom: Uom, itemCategory: ItemCategory, company: Company, partner: Partner, item: Item, itemUom: ItemUom, warehouse: Warehouse, order: OrderHeader, delivery: DeliveryHeader, deliveryDetail: DeliveryDetail, arInvoice: ArInvoiceHeader}
 */
function setupItemInvoiceFixtures(): array
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

    $partner = Partner::create([
        'code' => 'CUST01',
        'name' => 'Test Customer',
        'is_customer' => true,
        'is_supplier' => false,
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
        'min_stock' => 0,
        'max_stock' => 0,
        'is_active' => true,
    ]);

    $itemUom = ItemUom::create([
        'item_id' => $item->id,
        'uom_id' => $uom->id,
        'conversion_rate' => 1,
        'is_base' => true,
        'is_active' => true,
    ]);

    $warehouse = Warehouse::create([
        'code' => 'WH01',
        'name' => 'Main Warehouse',
        'is_active' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $order = OrderHeader::create([
        'code_request' => 'SR/TEST/0001',
        'code_order' => 'SO/TEST/0001',
        'date' => now()->format('Y-m-d'),
        'delivery_date' => now()->addDays(3)->format('Y-m-d'),
        'currency_id' => $currency->id,
        'partner_id' => $partner->id,
        'company_id' => $company->id,
        'item_category_id' => $itemCategory->id,
        'tax_mode' => 'NONE',
        'tax_rate' => 0,
        'status' => 'FINISH',
        'subtotal' => 500.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $user->id,
    ]);

    $delivery = DeliveryHeader::create([
        'code' => 'DO/TEST/0001',
        'date' => now(),
        'order_header_id' => $order->id,
        'partner_id' => $partner->id,
        'company_id' => $company->id,
        'currency_id' => $currency->id,
        'status' => 'finished',
        'subtotal' => 500.00,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $user->id,
    ]);

    $deliveryDetail = DeliveryDetail::create([
        'delivery_header_id' => $delivery->id,
        'order_detail_id' => null,
        'item_id' => $item->id,
        'item_uom_id' => $itemUom->id,
        'warehouse_id' => $warehouse->id,
        'quantity_sent' => 10.00,
        'quantity_received' => 10.00,
        'price' => 50.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $user->id,
    ]);

    $arInvoice = ArInvoiceHeader::create([
        'code' => 'INV/TEST/0001',
        'date' => now(),
        'due_date' => now()->addDays(30),
        'currency_id' => $currency->id,
        'partner_id' => $partner->id,
        'order_header_id' => $order->id,
        'delivery_header_id' => $delivery->id,
        'subtotal' => 500.00,
        'tax' => 0,
        'total' => 500.00,
        'paid' => 0,
        'balance' => 500.00,
        'status' => 'unpaid',
    ]);

    return compact(
        'user', 'currency', 'uom', 'itemCategory', 'company', 'partner',
        'item', 'itemUom', 'warehouse', 'order', 'delivery', 'deliveryDetail', 'arInvoice'
    );
}

function grantReturnPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'return-test-role', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user->assignRole($role);
}

/**
 * Create a ReturnHeader + ReturnDetail with ITEM_INVOICE type.
 *
 * @return array{header: ReturnHeader, detail: ReturnDetail}
 */
function createItemInvoiceReturn(array $fixtures, string $status = 'INIT'): array
{
    $header = ReturnHeader::create([
        'code' => 'RTN/TEST/0001',
        'transaction_type' => 'SO',
        'return_type' => ReturnHeader::TYPE_ITEM_INVOICE,
        'date' => now()->format('Y-m-d'),
        'order_header_id' => $fixtures['order']->id,
        'delivery_header_id' => $fixtures['delivery']->id,
        'ar_invoice_header_id' => $fixtures['arInvoice']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'currency_id' => $fixtures['currency']->id,
        'status' => $status,
        'subtotal' => 500.00,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $fixtures['user']->id,
        'updated_by' => $fixtures['user']->id,
    ]);

    $detail = ReturnDetail::create([
        'return_header_id' => $header->id,
        'delivery_detail_id' => $fixtures['deliveryDetail']->id,
        'item_id' => $fixtures['item']->id,
        'item_uom_id' => $fixtures['itemUom']->id,
        'quantity_return' => 10.00,
        'price' => 50.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $fixtures['user']->id,
        'updated_by' => $fixtures['user']->id,
    ]);

    return compact('header', 'detail');
}

// ──────────────────────────────────────────────────────────────────────────────
// Model Helpers
// ──────────────────────────────────────────────────────────────────────────────

it('identifies ITEM_INVOICE type via isItemInvoice()', function () {
    $fixtures = setupItemInvoiceFixtures();
    $return = createItemInvoiceReturn($fixtures);

    expect($return['header']->isItemInvoice())->toBeTrue();
    expect($return['header']->isItemType())->toBeFalse();
    expect($return['header']->isInvoiceType())->toBeFalse();
    expect($return['header']->isAllocationType())->toBeTrue();
});

it('groups ITEM and ITEM_INVOICE as allocation types', function () {
    $fixtures = setupItemInvoiceFixtures();

    $itemReturn = ReturnHeader::create([
        'code' => 'RTN/TEST/ITEM',
        'transaction_type' => 'SO',
        'return_type' => ReturnHeader::TYPE_ITEM,
        'date' => now()->format('Y-m-d'),
        'order_header_id' => $fixtures['order']->id,
        'delivery_header_id' => $fixtures['delivery']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'currency_id' => $fixtures['currency']->id,
        'status' => 'INIT',
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_by' => $fixtures['user']->id,
    ]);

    $itemInvoiceReturn = createItemInvoiceReturn($fixtures);

    expect($itemReturn->isAllocationType())->toBeTrue();
    expect($itemInvoiceReturn['header']->isAllocationType())->toBeTrue();
});

it('renders teal badge for ITEM_INVOICE type', function () {
    $badge = ReturnHeader::returnTypeHtmlBadge('ITEM_INVOICE');

    expect($badge)->toContain('ITEM_INVOICE');
    expect($badge)->toContain('teal');
});

// ──────────────────────────────────────────────────────────────────────────────
// Create Component
// ──────────────────────────────────────────────────────────────────────────────

it('allows creating a return with ITEM_INVOICE type', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], ['create sales return']);

    Livewire::actingAs($fixtures['user'])
        ->test(SalesReturnCreate::class, ['deliveryId' => $fixtures['delivery']->id])
        ->set('inputs.return_type', 'ITEM_INVOICE')
        ->set('inputs.date', now()->format('Y-m-d'))
        ->call('store')
        ->assertHasNoErrors();

    $return = ReturnHeader::where('delivery_header_id', $fixtures['delivery']->id)->first();
    expect($return)->not->toBeNull();
    expect($return->return_type)->toBe('ITEM_INVOICE');
    expect($return->status)->toBe('INIT');
});

// ──────────────────────────────────────────────────────────────────────────────
// Approval – ITEM_INVOICE reduces AR + goes to PROCESSING
// ──────────────────────────────────────────────────────────────────────────────

it('reduces AR balance and sets PROCESSING on ITEM_INVOICE approval', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'view sales return',
        'approve sales return',
    ]);

    $return = createItemInvoiceReturn($fixtures, 'APPROVAL');

    Livewire::actingAs($fixtures['user'])
        ->test(SalesReturnShow::class, ['id' => $return['header']->id])
        ->call('processApproval')
        ->assertHasNoErrors();

    $return['header']->refresh();
    $fixtures['arInvoice']->refresh();

    expect($return['header']->status)->toBe('PROCESSING');
    expect($return['header']->approved_by)->toBe($fixtures['user']->id);
    expect($return['header']->approved_at)->not->toBeNull();
    expect((float) $fixtures['arInvoice']->balance)->toBe(0.00);
});

it('does not finish ITEM_INVOICE on approval (unlike INVOICE_DISCARD)', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'view sales return',
        'approve sales return',
    ]);

    $return = createItemInvoiceReturn($fixtures, 'APPROVAL');

    Livewire::actingAs($fixtures['user'])
        ->test(SalesReturnShow::class, ['id' => $return['header']->id])
        ->call('processApproval');

    $return['header']->refresh();
    expect($return['header']->status)->not->toBe('FINISH');
    expect($return['header']->status)->toBe('PROCESSING');
});

// ──────────────────────────────────────────────────────────────────────────────
// Warehouse Receipt – ITEM_INVOICE stays PROCESSING (no double invoice cut)
// ──────────────────────────────────────────────────────────────────────────────

it('keeps ITEM_INVOICE return in PROCESSING after warehouse receipt', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'view warehouse return',
        'receive warehouse return',
    ]);

    $return = createItemInvoiceReturn($fixtures, 'PROCESSING');

    Livewire::actingAs($fixtures['user'])
        ->test(WarehouseReturnShow::class, ['id' => $return['header']->id])
        ->set('lines.0.quantity_received_good', 10)
        ->set('lines.0.quantity_received_damaged', 0)
        ->set('lines.0.warehouse_id', $fixtures['warehouse']->id)
        ->call('confirmReceipt')
        ->assertHasNoErrors();

    $return['header']->refresh();
    expect($return['header']->status)->toBe('PROCESSING');
    expect($return['header']->received_by)->toBe($fixtures['user']->id);
    expect($return['header']->received_at)->not->toBeNull();
});

it('does not reduce AR balance again during ITEM_INVOICE warehouse receipt', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'view warehouse return',
        'receive warehouse return',
    ]);

    // Simulate: AR was already reduced at approval
    $fixtures['arInvoice']->update(['balance' => 0.00, 'status' => 'paid']);

    $return = createItemInvoiceReturn($fixtures, 'PROCESSING');

    Livewire::actingAs($fixtures['user'])
        ->test(WarehouseReturnShow::class, ['id' => $return['header']->id])
        ->set('lines.0.quantity_received_good', 10)
        ->set('lines.0.quantity_received_damaged', 0)
        ->set('lines.0.warehouse_id', $fixtures['warehouse']->id)
        ->call('confirmReceipt');

    $fixtures['arInvoice']->refresh();
    expect((float) $fixtures['arInvoice']->balance)->toBe(0.00);
    expect($fixtures['arInvoice']->status)->toBe('paid');
});

// ──────────────────────────────────────────────────────────────────────────────
// Allocation – ITEM_INVOICE works same as ITEM
// ──────────────────────────────────────────────────────────────────────────────

it('allows allocation for ITEM_INVOICE type after warehouse receipt', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'view sales return',
        'edit sales return',
    ]);

    $return = createItemInvoiceReturn($fixtures, 'PROCESSING');

    // Simulate warehouse receipt
    $return['header']->update([
        'received_by' => $fixtures['user']->id,
        'received_at' => now(),
    ]);
    $return['detail']->update([
        'quantity_received_good' => 10.00,
        'quantity_received_damaged' => 0,
    ]);

    // Seed inventory for redelivery stock check
    InventoryLedger::create([
        'item_id' => $fixtures['item']->id,
        'warehouse_id' => $fixtures['warehouse']->id,
        'item_uom_id' => $fixtures['itemUom']->id,
        'quantity_in' => 10,
        'quantity_out' => 0,
        'balance' => 10,
        'date' => now()->toDateString(),
        'type' => 'sales_return',
        'reference_type' => ReturnHeader::class,
        'reference_id' => $return['header']->id,
        'created_by' => $fixtures['user']->id,
        'updated_by' => $fixtures['user']->id,
    ]);

    Livewire::actingAs($fixtures['user'])
        ->test(SalesReturnShow::class, ['id' => $return['header']->id])
        ->set('allocations.0.quantity_redelivery', 0)
        ->set('allocations.0.quantity_next_so', 10)
        ->call('processAllocation')
        ->assertHasNoErrors();

    $return['header']->refresh();
    $return['detail']->refresh();

    expect($return['header']->status)->toBe('FINISH');
    expect((float) $return['detail']->quantity_next_so)->toBe(10.00);
    expect((float) $return['detail']->quantity_redelivery)->toBe(0.00);
});

// ──────────────────────────────────────────────────────────────────────────────
// SR Create – ITEM_INVOICE return items use original SO price
// ──────────────────────────────────────────────────────────────────────────────

it('includes ITEM_INVOICE returns in SR return items with SO price', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'create sales request',
        'view sales return',
    ]);

    // Create a finished ITEM_INVOICE return with next_so allocation
    $return = createItemInvoiceReturn($fixtures, 'FINISH');
    $return['detail']->update([
        'quantity_next_so' => 5.00,
        'is_next_so_consumed' => false,
    ]);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Create::class);

    // Set the required fields to trigger loadReturnItems
    $component->set('inputs.partner_id', $fixtures['partner']->id)
        ->set('inputs.company_id', $fixtures['company']->id)
        ->set('inputs.item_category_id', $fixtures['itemCategory']->id)
        ->set('inputs.tax_mode', 'NONE')
        ->call('loadReturnItems');

    $returnItems = $component->get('returnItems');
    $itemInvoiceItem = collect($returnItems)->firstWhere('return_detail_id', $return['detail']->id);

    expect($itemInvoiceItem)->not->toBeNull();
    expect($itemInvoiceItem['return_type'])->toBe('ITEM_INVOICE');
    expect((float) $itemInvoiceItem['price'])->toBe(50.00);
});

it('uses SO price (not 0) for ITEM_INVOICE when creating SR order details', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'create sales request',
        'view sales return',
    ]);

    // Create a finished ITEM_INVOICE return with next_so allocation
    $return = createItemInvoiceReturn($fixtures, 'FINISH');
    $return['detail']->update([
        'quantity_next_so' => 5.00,
        'is_next_so_consumed' => false,
    ]);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Create::class);

    $component->set('inputs.partner_id', $fixtures['partner']->id)
        ->set('inputs.company_id', $fixtures['company']->id)
        ->set('inputs.item_category_id', $fixtures['itemCategory']->id)
        ->set('inputs.tax_mode', 'NONE')
        ->set('inputs.date', now()->format('Y-m-d'))
        ->call('loadReturnItems')
        ->call('toggleReturnItem', $return['detail']->id)
        ->call('store');

    $orderDetail = OrderDetail::where('return_detail_id', $return['detail']->id)->first();

    expect($orderDetail)->not->toBeNull();
    expect((float) $orderDetail->price_proposed)->toBe(50.00);
    expect((float) $orderDetail->price_deal)->toBe(50.00);
});

it('uses price 0 for ITEM type when creating SR order details', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'create sales request',
        'view sales return',
    ]);

    // Create a finished ITEM return with next_so allocation
    $returnHeader = ReturnHeader::create([
        'code' => 'RTN/TEST/ITEM01',
        'transaction_type' => 'SO',
        'return_type' => ReturnHeader::TYPE_ITEM,
        'date' => now()->format('Y-m-d'),
        'order_header_id' => $fixtures['order']->id,
        'delivery_header_id' => $fixtures['delivery']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'currency_id' => $fixtures['currency']->id,
        'status' => 'FINISH',
        'subtotal' => 500.00,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $fixtures['user']->id,
    ]);

    $returnDetail = ReturnDetail::create([
        'return_header_id' => $returnHeader->id,
        'delivery_detail_id' => $fixtures['deliveryDetail']->id,
        'item_id' => $fixtures['item']->id,
        'item_uom_id' => $fixtures['itemUom']->id,
        'quantity_return' => 10.00,
        'quantity_next_so' => 5.00,
        'is_next_so_consumed' => false,
        'price' => 50.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 500.00,
        'created_by' => $fixtures['user']->id,
    ]);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(Create::class);

    $component->set('inputs.partner_id', $fixtures['partner']->id)
        ->set('inputs.company_id', $fixtures['company']->id)
        ->set('inputs.item_category_id', $fixtures['itemCategory']->id)
        ->set('inputs.tax_mode', 'NONE')
        ->set('inputs.date', now()->format('Y-m-d'))
        ->call('loadReturnItems')
        ->call('toggleReturnItem', $returnDetail->id)
        ->call('store');

    $orderDetail = OrderDetail::where('return_detail_id', $returnDetail->id)->first();

    expect($orderDetail)->not->toBeNull();
    expect((float) $orderDetail->price_proposed)->toBe(0.00);
    expect((float) $orderDetail->price_deal)->toBe(0.00);
});

// ──────────────────────────────────────────────────────────────────────────────
// SR Edit – ITEM_INVOICE toggle uses SO price
// ──────────────────────────────────────────────────────────────────────────────

it('applies SO price when toggling ITEM_INVOICE return item in SR Edit', function () {
    $fixtures = setupItemInvoiceFixtures();
    grantReturnPermissions($fixtures['user'], [
        'edit sales request',
        'view sales return',
    ]);

    // Create a finished ITEM_INVOICE return with next_so allocation
    $return = createItemInvoiceReturn($fixtures, 'FINISH');
    $return['detail']->update([
        'quantity_next_so' => 5.00,
        'is_next_so_consumed' => false,
    ]);

    // Create an existing SR order for this partner
    $srOrder = OrderHeader::create([
        'code_request' => 'SR/TEST/0002',
        'date' => now()->format('Y-m-d'),
        'currency_id' => $fixtures['currency']->id,
        'partner_id' => $fixtures['partner']->id,
        'company_id' => $fixtures['company']->id,
        'item_category_id' => $fixtures['itemCategory']->id,
        'tax_mode' => 'NONE',
        'tax_rate' => 0,
        'status' => 'INIT',
        'subtotal' => 0,
        'discount' => 0,
        'tax' => 0,
        'total' => 0,
        'created_by' => $fixtures['user']->id,
    ]);

    $component = Livewire::actingAs($fixtures['user'])
        ->test(SalesRequestEdit::class, ['id' => $srOrder->id]);

    $component->call('loadReturnItems');

    $returnItems = $component->get('returnItems');
    $itemInvoiceItem = collect($returnItems)->firstWhere('return_detail_id', $return['detail']->id);

    expect($itemInvoiceItem)->not->toBeNull();
    expect($itemInvoiceItem['return_type'])->toBe('ITEM_INVOICE');
    expect((float) $itemInvoiceItem['price'])->toBe(50.00);

    $component->call('toggleReturnItem', $return['detail']->id);

    $items = $component->get('items');
    $addedItem = collect($items)->firstWhere('return_detail_id', $return['detail']->id);

    expect($addedItem)->not->toBeNull();
    expect((float) $addedItem['price_proposed'])->toBe(50.00);
    expect((float) $addedItem['price_deal'])->toBe(50.00);
});
