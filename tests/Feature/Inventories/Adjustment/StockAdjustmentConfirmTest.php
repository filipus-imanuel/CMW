<?php

use App\Livewire\Inventories\Adjustment\Show;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\Warehouse;
use App\Models\CMW\Transaction\StockAdjustmentDetail;
use App\Models\CMW\Transaction\StockAdjustmentHeader;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Create all required records for stock adjustment tests.
 *
 * @return array{user: User, warehouse: Warehouse, item: Item, itemUomBase: ItemUom, itemUomRoll: ItemUom}
 */
function setupAdjustmentFixtures(float $conversionRate = 100): array
{
    $currency = Currency::create([
        'code' => 'IDR',
        'name' => 'Rupiah',
        'symbol' => 'Rp',
        'is_active' => true,
    ]);

    $uomPcs = Uom::create([
        'code' => 'PCS',
        'name' => 'Pieces',
        'is_active' => true,
    ]);

    $uomRoll = Uom::create([
        'code' => 'ROLL',
        'name' => 'Roll',
        'is_active' => true,
    ]);

    $itemCategory = ItemCategory::create([
        'code' => 'CAT01',
        'name' => 'Test Category',
        'is_active' => true,
    ]);

    $warehouse = Warehouse::create([
        'code' => 'WH01',
        'name' => 'Main Warehouse',
        'is_active' => true,
    ]);

    $item = Item::create([
        'code' => 'ITEM01',
        'name' => 'Test Item',
        'type' => 'FG',
        'item_category_id' => $itemCategory->id,
        'currency_id' => $currency->id,
        'cost_price' => 50.00,
        'sell_price' => 100.00,
        'min_stock' => 0,
        'max_stock' => 0,
        'is_active' => true,
    ]);

    $itemUomBase = ItemUom::create([
        'item_id' => $item->id,
        'uom_id' => $uomPcs->id,
        'conversion_rate' => 1,
        'is_base' => true,
        'is_active' => true,
    ]);

    $itemUomRoll = ItemUom::create([
        'item_id' => $item->id,
        'uom_id' => $uomRoll->id,
        'conversion_rate' => $conversionRate,
        'is_base' => false,
        'is_active' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    return compact('user', 'warehouse', 'item', 'itemUomBase', 'itemUomRoll');
}

function grantAdjustmentPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'adjustment-test-role', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);
    $user->assignRole($role);
}

/**
 * Seed an initial inventory ledger balance for an item in a warehouse.
 */
function seedBalance(int $itemId, int $warehouseId, float $baseBalance, int $userId): InventoryLedger
{
    return InventoryLedger::create([
        'item_id' => $itemId,
        'warehouse_id' => $warehouseId,
        'date' => now()->subDay()->toDateString(),
        'type' => 'initial',
        'reference_type' => null,
        'reference_id' => null,
        'quantity_in' => $baseBalance,
        'quantity_out' => 0,
        'balance' => $baseBalance,
        'unit_cost' => 50.00,
        'created_by' => $userId,
    ]);
}

// ──────────────────────────────────────────────────────────────────────────────
// CONFIRM — UOM conversion
// ──────────────────────────────────────────────────────────────────────────────

it('converts quantity_difference to base UOM when confirming with non-base UOM', function () {
    $f = setupAdjustmentFixtures(100); // 1 Roll = 100 PCS
    grantAdjustmentPermissions($f['user'], [
        'view stock adjustment', 'confirm stock adjustment',
    ]);

    // Seed 1000 PCS (= 10 Rolls) in warehouse
    seedBalance($f['item']->id, $f['warehouse']->id, 1000, $f['user']->id);

    // Create header + detail: system=10 Rolls, actual=15 Rolls, diff=+5 Rolls
    $header = StockAdjustmentHeader::create([
        'code' => 'ADJ/TEST/001',
        'date' => now()->format('Y-m-d'),
        'warehouse_id' => $f['warehouse']->id,
        'status' => StockAdjustmentHeader::STATUS_DRAFT,
        'created_by' => $f['user']->id,
    ]);

    StockAdjustmentDetail::create([
        'stock_adjustment_header_id' => $header->id,
        'item_id' => $f['item']->id,
        'item_uom_id' => $f['itemUomRoll']->id,
        'warehouse_id' => $f['warehouse']->id,
        'quantity_system' => 10,
        'quantity_actual' => 15,
        'quantity_difference' => 5, // 5 Rolls
        'created_by' => $f['user']->id,
    ]);

    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $header->id])
        ->call('confirm')
        ->assertHasNoErrors();

    // Ledger should have 500 PCS in (5 Rolls × 100), not 5
    $ledgerEntry = InventoryLedger::where('reference_type', StockAdjustmentHeader::class)
        ->where('reference_id', $header->id)
        ->first();

    expect($ledgerEntry)->not->toBeNull();
    expect((float) $ledgerEntry->quantity_in)->toBe(500.00);
    expect((float) $ledgerEntry->quantity_out)->toBe(0.00);
    expect((float) $ledgerEntry->balance)->toBe(1500.00); // 1000 + 500
});

it('converts negative quantity_difference to base UOM when confirming', function () {
    $f = setupAdjustmentFixtures(100);
    grantAdjustmentPermissions($f['user'], [
        'view stock adjustment', 'confirm stock adjustment',
    ]);

    seedBalance($f['item']->id, $f['warehouse']->id, 1000, $f['user']->id);

    $header = StockAdjustmentHeader::create([
        'code' => 'ADJ/TEST/002',
        'date' => now()->format('Y-m-d'),
        'warehouse_id' => $f['warehouse']->id,
        'status' => StockAdjustmentHeader::STATUS_DRAFT,
        'created_by' => $f['user']->id,
    ]);

    StockAdjustmentDetail::create([
        'stock_adjustment_header_id' => $header->id,
        'item_id' => $f['item']->id,
        'item_uom_id' => $f['itemUomRoll']->id,
        'warehouse_id' => $f['warehouse']->id,
        'quantity_system' => 10,
        'quantity_actual' => 7,
        'quantity_difference' => -3, // -3 Rolls
        'created_by' => $f['user']->id,
    ]);

    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $header->id])
        ->call('confirm')
        ->assertHasNoErrors();

    $ledgerEntry = InventoryLedger::where('reference_type', StockAdjustmentHeader::class)
        ->where('reference_id', $header->id)
        ->first();

    expect($ledgerEntry)->not->toBeNull();
    expect((float) $ledgerEntry->quantity_in)->toBe(0.00);
    expect((float) $ledgerEntry->quantity_out)->toBe(300.00); // 3 Rolls × 100
    expect((float) $ledgerEntry->balance)->toBe(700.00); // 1000 - 300
});

it('works correctly with base UOM (conversion_rate=1)', function () {
    $f = setupAdjustmentFixtures(100);
    grantAdjustmentPermissions($f['user'], [
        'view stock adjustment', 'confirm stock adjustment',
    ]);

    seedBalance($f['item']->id, $f['warehouse']->id, 1000, $f['user']->id);

    $header = StockAdjustmentHeader::create([
        'code' => 'ADJ/TEST/003',
        'date' => now()->format('Y-m-d'),
        'warehouse_id' => $f['warehouse']->id,
        'status' => StockAdjustmentHeader::STATUS_DRAFT,
        'created_by' => $f['user']->id,
    ]);

    // Use base UOM (PCS, conversion_rate=1)
    StockAdjustmentDetail::create([
        'stock_adjustment_header_id' => $header->id,
        'item_id' => $f['item']->id,
        'item_uom_id' => $f['itemUomBase']->id,
        'warehouse_id' => $f['warehouse']->id,
        'quantity_system' => 1000,
        'quantity_actual' => 1050,
        'quantity_difference' => 50, // 50 PCS
        'created_by' => $f['user']->id,
    ]);

    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $header->id])
        ->call('confirm')
        ->assertHasNoErrors();

    $ledgerEntry = InventoryLedger::where('reference_type', StockAdjustmentHeader::class)
        ->where('reference_id', $header->id)
        ->first();

    expect((float) $ledgerEntry->quantity_in)->toBe(50.00); // 50 × 1 = 50
    expect((float) $ledgerEntry->balance)->toBe(1050.00);
});

// ──────────────────────────────────────────────────────────────────────────────
// CANCEL — reversal UOM conversion
// ──────────────────────────────────────────────────────────────────────────────

it('converts reversal entries to base UOM when cancelling a confirmed adjustment', function () {
    $f = setupAdjustmentFixtures(100);
    grantAdjustmentPermissions($f['user'], [
        'view stock adjustment', 'confirm stock adjustment', 'cancel stock adjustment',
    ]);

    seedBalance($f['item']->id, $f['warehouse']->id, 1000, $f['user']->id);

    $header = StockAdjustmentHeader::create([
        'code' => 'ADJ/TEST/004',
        'date' => now()->format('Y-m-d'),
        'warehouse_id' => $f['warehouse']->id,
        'status' => StockAdjustmentHeader::STATUS_DRAFT,
        'created_by' => $f['user']->id,
    ]);

    StockAdjustmentDetail::create([
        'stock_adjustment_header_id' => $header->id,
        'item_id' => $f['item']->id,
        'item_uom_id' => $f['itemUomRoll']->id,
        'warehouse_id' => $f['warehouse']->id,
        'quantity_system' => 10,
        'quantity_actual' => 15,
        'quantity_difference' => 5,
        'created_by' => $f['user']->id,
    ]);

    // Confirm first
    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $header->id])
        ->call('confirm');

    // Balance should now be 1500
    $balanceAfterConfirm = InventoryLedger::where('item_id', $f['item']->id)
        ->where('warehouse_id', $f['warehouse']->id)
        ->orderByDesc('id')
        ->value('balance');
    expect((float) $balanceAfterConfirm)->toBe(1500.00);

    // Now cancel
    Livewire::actingAs($f['user'])
        ->test(Show::class, ['id' => $header->id])
        ->call('cancel');

    // Reversal should subtract 500 PCS (5 Rolls × 100)
    $reversalEntry = InventoryLedger::where('reference_type', StockAdjustmentHeader::class)
        ->where('reference_id', $header->id)
        ->where('remarks', 'LIKE', 'REVERSAL%')
        ->first();

    expect($reversalEntry)->not->toBeNull();
    expect((float) $reversalEntry->quantity_out)->toBe(500.00);
    expect((float) $reversalEntry->quantity_in)->toBe(0.00);
    expect((float) $reversalEntry->balance)->toBe(1000.00); // Back to original
});
