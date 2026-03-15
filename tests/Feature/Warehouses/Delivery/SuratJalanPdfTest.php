<?php

use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\Warehouse;
use App\Models\CMW\Transaction\DeliveryDetail;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function setupDeliveryPdfFixtures(): array
{
    $currency = Currency::create([
        'code' => 'IDR',
        'name' => 'Rupiah',
        'symbol' => 'Rp',
        'is_active' => true,
    ]);

    $uom = Uom::create([
        'code' => 'KG',
        'name' => 'Kilogram',
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
        'name' => 'Plastik PE',
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

    $warehouse = Warehouse::create([
        'code' => 'WH01',
        'name' => 'Main Warehouse',
        'is_active' => true,
    ]);

    $role = Role::findOrCreate('warehouse', 'web');
    Permission::findOrCreate('view delivery order', 'web');
    $role->givePermissionTo('view delivery order');

    $user = User::factory()->create();
    $user->assignRole($role);

    $order = OrderHeader::create([
        'code_request' => 'SR/2603/0001',
        'code_order' => 'SO/2603/0001',
        'date' => now(),
        'delivery_date' => now()->addDays(3),
        'currency_id' => $currency->id,
        'partner_id' => $partner->id,
        'company_id' => $company->id,
        'item_category_id' => $itemCategory->id,
        'status' => 'DELIVERY',
        'subtotal' => 1000.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 1000.00,
        'created_by' => $user->id,
    ]);

    $delivery = DeliveryHeader::create([
        'code' => 'DO/2603/0001',
        'date' => now(),
        'order_header_id' => $order->id,
        'partner_id' => $partner->id,
        'company_id' => $company->id,
        'currency_id' => $currency->id,
        'status' => 'ongoing',
        'subtotal' => 1000.00,
        'tax' => 0,
        'total' => 1000.00,
        'vehicle_number' => 'W 8150 NE',
        'delivery_address' => 'Surabaya, Jawa Timur',
        'created_by' => $user->id,
    ]);

    $itemUom = ItemUom::create([
        'item_id' => $item->id,
        'uom_id' => $uom->id,
        'conversion_factor' => 1,
        'is_active' => true,
    ]);

    DeliveryDetail::create([
        'delivery_header_id' => $delivery->id,
        'order_detail_id' => null,
        'item_id' => $item->id,
        'item_uom_id' => $itemUom->id,
        'warehouse_id' => $warehouse->id,
        'quantity_sent' => 206.00,
        'quantity_received' => 0,
        'price' => 5.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 1030.00,
        'created_by' => $user->id,
    ]);

    return compact('user', 'delivery', 'order');
}

it('downloads surat jalan PDF for ongoing delivery', function () {
    $fixtures = setupDeliveryPdfFixtures();

    $response = $this->actingAs($fixtures['user'])
        ->get(route('warehouses.delivery.surat-jalan', $fixtures['delivery']->id));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

it('downloads surat jalan PDF for finished delivery', function () {
    $fixtures = setupDeliveryPdfFixtures();
    $fixtures['delivery']->update(['status' => 'finished']);

    $response = $this->actingAs($fixtures['user'])
        ->get(route('warehouses.delivery.surat-jalan', $fixtures['delivery']->id));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

it('forbids surat jalan PDF for cancelled delivery', function () {
    $fixtures = setupDeliveryPdfFixtures();
    $fixtures['delivery']->update(['status' => 'cancelled']);

    $response = $this->actingAs($fixtures['user'])
        ->get(route('warehouses.delivery.surat-jalan', $fixtures['delivery']->id));

    $response->assertForbidden();
});

it('forbids surat jalan PDF without permission', function () {
    $fixtures = setupDeliveryPdfFixtures();

    $userWithoutPermission = User::factory()->create();

    $response = $this->actingAs($userWithoutPermission)
        ->get(route('warehouses.delivery.surat-jalan', $fixtures['delivery']->id));

    $response->assertForbidden();
});

it('returns 404 for non-existent delivery surat jalan', function () {
    $fixtures = setupDeliveryPdfFixtures();

    $response = $this->actingAs($fixtures['user'])
        ->get(route('warehouses.delivery.surat-jalan', 99999));

    $response->assertNotFound();
});
