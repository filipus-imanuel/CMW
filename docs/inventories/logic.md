# Inventory Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The Inventory module manages **items** (products/materials), their **categorization**, **multi-UOM support**, **pricing tiers**, and **stock movements**. It underpins Sales, Purchase, Production, and Warehouse workflows.

### Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Item | `items` | `App\Models\CMW\Inventory\Item` | Master product/material record |
| Item Category | `item_categories` | `App\Models\CMW\Inventory\ItemCategory` | Grouping items (e.g. PP Film, Stretch Film) |
| **Item UOM** | `item_uoms` | `App\Models\CMW\Inventory\ItemUom` | Valid UOMs per item with conversion rates |
| Category Price | `category_prices` | `App\Models\CMW\Inventory\CategoryPrice` | Pricing tier (e.g. GEN, VIP, DISTRIBUTOR) |
| Item Price | `item_prices` | `App\Models\CMW\Inventory\ItemPrice` | Concrete price per **ItemUom** × CategoryPrice |
| Pending Item Price | `item_prices_pending` | `App\Models\CMW\Inventory\PendingItemPrice` | Approval workflow for price changes |
| History Item Price | `history_item_prices` | `App\Models\CMW\History\HistoryItemPrice` | Audit log of all price mutations |
| Inventory Ledger | `inventory_ledgers` | `App\Models\CMW\Inventory\InventoryLedger` | Stock movement journal (future phase) |

---

## 2. Multi-UOM Design

### 2.1 Concept

Each item can have **multiple units of measure** (e.g., Pcs, Dus, Roll). One UOM is marked as **base** (`is_base = true`) which is the stock-keeping unit. Other UOMs have a `conversion_rate` expressing how many base units equal one of that UOM.

### 2.2 Item UOM Table (`item_uoms`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `item_id` | FK → items | Parent item |
| `uom_id` | FK → uoms | Master UOM reference |
| `conversion_rate` | decimal(13,4) | How many base units per 1 of this UOM |
| `is_base` | boolean | Base UOM flag (exactly 1 per item) |
| `remarks` | varchar(1024) | Optional notes |

**Constraints**: `unique(item_id, uom_id)`, `index(item_id, is_base)`

### 2.3 Conversion Rate Rules

- Base UOM always has `conversion_rate = 1.0000`
- Example: Item "PP Film Roll" has base UOM = Lembar (sheet)
  - Lembar: conversion_rate = 1 (base)
  - Roll: conversion_rate = 500 (1 Roll = 500 Lembar)
- Inventory ledger always records quantities in **base UOM**

---

## 3. Relationship Map

```
Partner ──belongsTo──▶ CategoryPrice
    │
    ▼
OrderHeader ──hasMany──▶ OrderDetail ──belongsTo──▶ Item
    │                        │                        │
    ├──belongsTo──▶ Company  ├──belongsTo──▶ ItemUom  ├──belongsTo──▶ ItemCategory
    ├──belongsTo──▶ ItemCat  │                        ├──hasMany──▶ ItemUom ──belongsTo──▶ Uom
    └──belongsTo──▶ Partner  │                        │               │
                             │                        │               └──hasMany──▶ ItemPrice ──belongsTo──▶ CategoryPrice
                             │                        ├──belongsTo──▶ Currency
                             │                        ├──belongsTo──▶ Warehouse (default_warehouse_id)
                             │                        ├──hasMany──▶ ItemWarehouse ──belongsTo──▶ Warehouse
                             │                        ├──hasMany (through)──▶ ItemPrice
                             │                        ├──hasMany (through)──▶ HistoryItemPrice
                             │                        ├──hasMany──▶ BomHeader
                             │                        └──hasMany──▶ InventoryLedger ──belongsTo──▶ Warehouse
                             │                                                        └──morphTo──▶ reference
                             │
                             └──(item_uom_id refers back to item_uoms for UOM + conversion context)

ItemPrice ──hasMany──▶ PendingItemPrice ──belongsTo──▶ User (submittedBy, approvedBy)

ItemCategory ──belongsToMany──▶ Company (pivot: company_item_category)
             ──hasMany──▶ CompanySetting

Company ──belongsToMany──▶ Warehouse (pivot: company_warehouses)

Warehouse ──belongsToMany──▶ Company (pivot: company_warehouses)
          ──belongsToMany──▶ Item (pivot: item_warehouses)
          ──hasMany──▶ InventoryLedger
```

### Cardinality Summary

| From | Relation | To | FK |
|------|----------|----|----|
| Item | N:1 | ItemCategory | `item_category_id` |
| Item | N:1 | Currency | `currency_id` |
| Item | 1:N | ItemUom | `item_id` |
| Item | 1:1 | ItemUom (base) | `item_id` + `is_base=true` |
| ItemUom | N:1 | Item | `item_id` |
| ItemUom | N:1 | Uom | `uom_id` |
| ItemUom | 1:N | ItemPrice | `item_uom_id` |
| ItemPrice | N:1 | ItemUom | `item_uom_id` |
| ItemPrice | N:1 | CategoryPrice | `category_price_id` |
| ItemPrice | Unique | (item_uom_id, category_price_id) | composite unique |
| PendingItemPrice | N:1 | ItemPrice | `item_price_id` |
| PendingItemPrice | N:1 | ItemUom | `item_uom_id` |
| PendingItemPrice | N:1 | CategoryPrice | `category_price_id` |
| HistoryItemPrice | N:1 | ItemUom | `item_uom_id` |
| Partner | N:1 | CategoryPrice | `category_price_id` |
| OrderDetail | N:1 | ItemUom | `item_uom_id` |
| Item | N:1 | Warehouse (default) | `default_warehouse_id` |
| Item | M:N | Warehouse | pivot: `item_warehouses` |
| ItemWarehouse | N:1 | Item | `item_id` |
| ItemWarehouse | N:1 | Warehouse | `warehouse_id` |
| Company | M:N | Warehouse | pivot: `company_warehouses` |
| InventoryLedger | N:1 | Warehouse | `warehouse_id` |
| InventoryLedger | morph | reference | `reference_type` + `reference_id` |

---

## 4. Item Type Standards

Items use **UPPER_SNAKE_CASE** type values consistently across UI, database, and seeders:

| Type | Description |
|------|-------------|
| `RAW_MATERIAL` | Raw materials for production |
| `WORK_IN_PROCESS` | Semi-finished goods in production |
| `FINISHED_GOOD` | Completed products ready for sale |
| `SPARE_PART` | Spare parts and consumables |

**Validation rule**: `'required|in:RAW_MATERIAL,WORK_IN_PROCESS,FINISHED_GOOD,SPARE_PART'`

---

## 5. Pricing Resolution (Source of Truth)

### 5.1 Fallback Order

When determining the selling price for an item in a transaction, the system follows this **3-tier fallback**:

```
┌─────────────────────────────────────────────────────┐
│ 1. Customer Category Price                          │
│    Partner.category_price_id → ItemPrice            │
│    (item_uom_id + customer's category_price_id)     │
├──────────────── not found? ─────────────────────────┤
│ 2. General (GEN) Category Price                     │
│    CategoryPrice.code = 'GEN' → ItemPrice           │
│    (item_uom_id + GEN category_price_id)            │
├──────────────── not found? ─────────────────────────┤
│ 3. Item Master Sell Price                           │
│    Item.sell_price                                  │
└─────────────────────────────────────────────────────┘
```

### 5.2 Implementation

- **Helper**: `App\Helpers\CMW\PriceResolutionHelper`
  - `resolve(Item $item, ?Partner $partner, ?int $itemUomId)` — single item lookup (defaults to base UOM)
  - `resolveMany(Collection $items, ?Partner $partner)` — batch-optimized (uses base UOM per item)
- **Return format**: `['price' => float, 'source' => string, 'category_price_id' => int|null, 'item_uom_id' => int|null]`
- **Sources**: `'customer_category'`, `'general_category'`, `'item_sell_price'`

### 5.3 Usage in Sales Flow

1. User opens **Sales Request → Edit** and clicks "Add Item"
2. `SearchItem` component receives `itemCategoryId` + `partnerId` from the order context
3. Items are searched and prices resolved via `PriceResolutionHelper::resolveMany()`
4. Resolved price is dispatched as `sellPrice` + `itemUomId` (base UOM) to the Edit component
5. Price populates the order detail row (user can still override manually)

### 5.4 Price Change Workflow

1. **Direct update** — if change % ≤ threshold (`inventory.item_price.threshold_bypass_approval`): updates `ItemPrice` + writes `HistoryItemPrice` immediately
2. **Approval required** — if change % > threshold: creates `PendingItemPrice` with status `pending`
3. **Approval** — authorized user approves/rejects batch; on approve: updates `ItemPrice` + writes `HistoryItemPrice`; self-approval blocked (except Super Admin)

---

## 6. ItemPrice Soft-Delete Policy

### Rule: **Restore, Never Recreate**

The `item_prices` table has a composite unique constraint on `(item_uom_id, category_price_id)`. When a combination is soft-deleted:

- **Creating** the same combination checks `onlyTrashed()` first
- If a trashed record exists → **restore** it and update with new price
- History log records the old price from the restored record
- This preserves the original record ID and audit trail continuity

### Implications

- The unique constraint remains effective across active records
- No orphaned duplicate rows accumulate over time
- Price history stays linked to the same `item_uom_id + category_price_id` pair

---

## 7. Transaction Detail UOM Pattern

All transaction detail tables (`order_details`, `purchase_order_details`, `goods_receipt_details`, `purchase_return_details`, `production_consume_details`, `stock_adjustment_details`, `transfer_details`, `bom_details`, `production_headers`) reference `item_uom_id` → `item_uoms` instead of `uom_id` → `uoms`. This ensures:

- Only valid UOMs for the specific item can be selected
- Conversion rates are always available (via `ItemUom.conversion_rate`)
- Inventory ledger entries can be calculated in base UOM from the transaction UOM

---

## 8. Warehouse Assignment Design

### 8.1 Three-layer Warehouse Attachment

| Layer | Table | Purpose |
|-------|-------|---------|
| Company → Warehouse | `company_warehouses` (pivot) | Which warehouses belong to a company |
| Item → Warehouse (whitelist) | `item_warehouses` | Which warehouses are allowed to hold this item |
| Item default warehouse | `items.default_warehouse_id` | UX convenience — auto-fill on transaction entry |

These three layers are **independent but related**: the default warehouse must always be a member of the item's whitelist.

### 8.2 Item Warehouse Whitelist (`item_warehouses`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `item_id` | FK → items | Parent item |
| `warehouse_id` | FK → warehouses | Allowed warehouse |
| `is_active` | boolean | Whether assignment is active |

**Constraints**: `unique(item_id, warehouse_id)`, SoftDeletes. The soft-delete + restore pattern mirrors `ItemPrice`: if a trashed `(item_id, warehouse_id)` combination is re-added, it is **restored** rather than recreated.

### 8.3 Default Warehouse

`items.default_warehouse_id` (nullable FK → `warehouses`, `nullOnDelete`) is purely a UX hint — it auto-populates the warehouse field when this item is selected in a transaction form. It must always reference a warehouse that exists in `item_warehouses` for the same item; this is enforced at the Livewire layer (not at DB level).

### 8.4 Impact on Transaction Forms

When a warehouse is selected at the header level of a transaction (e.g., Stock Adjustment, Transfer), the **items dropdown is filtered** to only show items whose `item_warehouses` whitelist includes that warehouse. This uses `PopulateDataHelper::getItemsByWarehouse(int $warehouseId)`.

### 8.5 Transfer Validation

For warehouse transfers (`transfer_headers.warehouse_from_id` / `warehouse_to_id`), both warehouses must be in the item's whitelist — validated independently:
- Item must exist in `item_warehouses` for `warehouse_from_id`
- Item must exist in `item_warehouses` for `warehouse_to_id`

UOM selection for the transferred quantity is **not bound to a specific warehouse** — any valid `item_uom_id` for the item may be used.

---

## 9. Inventory Ledger & Stock Tracking

The `InventoryLedger` model and `inventory_ledgers` table are actively used by Stock Adjustment (see [adjustment_logic.md](adjustment_logic.md)) and Warehouse Return modules. The design supports:

- **Polymorphic references** (`reference_type` / `reference_id`) to link back to source documents (GR, Sales Delivery, Transfer, Adjustment, Production, etc.)
- **Per-warehouse running balance** via `balance` column (always in **base UOM**)
- **Indexed** on `(item_id, warehouse_id, date)` and `(reference_type, reference_id)`

### 9.1 Transfer Ledger Pattern

A single warehouse transfer produces **two ledger rows** within one DB transaction:

```
item_id | warehouse_id     | type          | qty_in | qty_out | balance
--------|------------------|---------------|--------|---------|--------
1       | warehouse_from   | transfer_out  | 0      | 20      | 50   ← balance in source warehouse
1       | warehouse_to     | transfer_in   | 20     | 0       | 20   ← balance in destination warehouse
```

### 9.2 Atomic Write Pattern

Every ledger write must use `lockForUpdate()` on the previous balance row to prevent race conditions:

```php
DB::transaction(function () use ($itemId, $warehouseId, $qtyIn, $qtyOut) {
    $lastBalance = InventoryLedger::where('item_id', $itemId)
        ->where('warehouse_id', $warehouseId)
        ->lockForUpdate()
        ->orderByDesc('id')
        ->value('balance') ?? 0;

    InventoryLedger::create([
        'item_id'      => $itemId,
        'warehouse_id' => $warehouseId,
        'quantity_in'  => $qtyIn,
        'quantity_out' => $qtyOut,
        'balance'      => $lastBalance + $qtyIn - $qtyOut,
        // ...
    ]);
});
```

### 9.3 Current Stock Query

Until a dedicated `item_warehouse_stocks` cache table is added, current stock is queried directly from the ledger:

```php
// Single item + warehouse
InventoryLedger::where('item_id', $itemId)
    ->where('warehouse_id', $warehouseId)
    ->orderByDesc('id')
    ->value('balance');

// All warehouses for one item
InventoryLedger::where('item_id', $itemId)
    ->whereIn('id', fn ($q) => $q
        ->selectRaw('MAX(id)')
        ->from('inventory_ledgers')
        ->where('item_id', $itemId)
        ->groupBy('warehouse_id')
    )
    ->get(['warehouse_id', 'balance']);
```

### 9.4 Future: `item_warehouse_stocks` Cache Table

When ledger volume grows, a denormalized cache table `item_warehouse_stocks` (`item_id`, `warehouse_id`, `item_uom_id` base, `quantity`) can be added. It must be:
- Written **atomically within the same DB transaction** as every ledger insert
- Seeded from aggregate of existing ledger on activation
- Accompanied by an artisan command `recalculate:stock` for reconciliation

When activated, all stock queries can use this table instead of aggregating the ledger.

When the posting service is built, all stock-affecting transactions must write ledger entries within their DB transaction scope, converting quantities to base UOM using `ItemUom.conversion_rate`.

---

## 10. Database Constraints & Indexes

| Table | Constraint | Type |
|-------|-----------|------|
| `items` | `code` | Unique |
| `items` | `default_warehouse_id` | Nullable FK → `warehouses`, nullOnDelete |
| `item_categories` | `code` | Unique |
| `item_warehouses` | `(item_id, warehouse_id)` | Unique (composite) |
| `item_warehouses` | SoftDeletes | Yes |
| `company_warehouses` | `(company_id, warehouse_id)` | Primary key (composite) |
| `category_prices` | `code` | Unique |
| `item_uoms` | `(item_id, uom_id)` | Unique (composite) |
| `item_uoms` | `(item_id, is_base)` | Index |
| `item_prices` | `(item_uom_id, category_price_id)` | Unique (composite) |
| `item_prices` | SoftDeletes | Yes |
| `history_item_prices` | `(item_uom_id, category_price_id, created_at)` | Index |
| `item_prices_pending` | `(item_price_id, status)` | Index |
| `item_prices_pending` | `(status, submitted_at)` | Index |
| `inventory_ledgers` | `(item_id, warehouse_id, date)` | Index |
| `inventory_ledgers` | `(reference_type, reference_id)` | Index |

---

## 11. Known Limitations & Future Considerations

1. **Ledger partially active** — Stock Adjustment and Warehouse Return write ledger entries; other stock-affecting transactions (Sales Delivery, Purchase GR, Transfer) do not yet write ledger entries
2. **item_warehouse_stocks not yet built** — current stock must be queried by aggregating `inventory_ledgers`; the cache table is deferred until the posting service is implemented (see §9.4)
3. **Default warehouse soft constraint** — `items.default_warehouse_id` is enforced at Livewire layer only (must be in `item_warehouses` whitelist); there is intentionally no DB-level check to avoid constraint complexity on soft-deleted rows
4. **Transfer warehouse validation** — item whitelist check on both `warehouse_from_id` and `warehouse_to_id` is deferred to transaction form validation; not enforced at DB level
5. **Company → Warehouse pivot is simple** — `company_warehouses` uses timestamps only (no `is_active`, no soft deletes) matching `company_item_category` pattern; elevate to full model if business rules require per-assignment status
6. **Category price assignment** — currently stored on Partner directly; may need per-company or per-item-category granularity in the future
7. **Price history cleanup** — `cleanup:item-price-history` command retains records for configurable months (default 12), uses `created_at` column
8. **Item.sell_price / cost_price** — serve as last-resort fallback (assumed to be base-UOM prices); should be kept reasonably up to date as a safety net
9. **Multi-UOM price creation** — Item create/edit screens auto-generate price rows for all category prices × all UOMs; standalone ItemPrice CRUD uses `item_uom_id` selector

---

## 12. Permission Matrix

| Permission | Actions |
|------------|----------|
| `category price` | `view`, `create`, `edit`, `delete` |
| `item` | `view`, `create`, `edit`, `delete` |
| `item category` | `view`, `create`, `edit`, `delete` |
| `item price` | `view`, `create`, `edit`, `delete` |
| `item price approval` | `view`, `approve`, `reject` |
| `item price history` | `view` |
| `stock adjustment` | `view`, `create`, `edit`, `confirm`, `cancel` |

---

## 13. Routes

| Route Name | URL | Component |
|------------|-----|----------|
| `inventories.category-prices.index` | `/cmw/inventories/category-prices` | `Inventories\CategoryPrice\Index` |
| `inventories.item-categories.index` | `/cmw/inventories/item-categories` | `Inventories\ItemCategory\Index` |
| `inventories.item-price-approval-history.index` | `/cmw/inventories/item-price-approval-history` | `Inventories\ItemPrice\ApprovalHistory` |
| `inventories.item-price-approvals.index` | `/cmw/inventories/item-price-approvals` | `Inventories\ItemPrice\Approval` |
| `inventories.item-price-history.index` | `/cmw/inventories/item-price-history` | `Inventories\HistoryItemPrice\Index` |
| `inventories.item-prices.index` | `/cmw/inventories/item-prices` | `Inventories\ItemPrice\Index` |
| `inventories.items.index` | `/cmw/inventories/items` | `Inventories\Item\Index` |
| `inventories.items.create` | `/cmw/inventories/items/create` | `Inventories\Item\Create` |
| `inventories.items.edit` | `/cmw/inventories/items/{id}/edit` | `Inventories\Item\Edit` |
| `inventories.stock-adjustments.index` | `/cmw/inventories/stock-adjustments` | `Inventories\Adjustment\Index` |
| `inventories.stock-adjustments.create` | `/cmw/inventories/stock-adjustments/create` | `Inventories\Adjustment\Create` |
| `inventories.stock-adjustments.edit` | `/cmw/inventories/stock-adjustments/{id}/edit` | `Inventories\Adjustment\Edit` |
| `inventories.stock-adjustments.show` | `/cmw/inventories/stock-adjustments/{id}` | `Inventories\Adjustment\Show` |

---

## 14. Sub-Modules

| Sub-Module | Docs | Description |
|------------|------|-------------|
| Stock Adjustment | [adjustment_logic.md](adjustment_logic.md) | Reconcile physical vs system stock, creates/reverses InventoryLedger entries |

---

## 15. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Inventory/` |
| Item Model | `app/Models/CMW/Inventory/Item.php` |
| ItemUom Model | `app/Models/CMW/Inventory/ItemUom.php` |
| ItemWarehouse Model | `app/Models/CMW/Inventory/ItemWarehouse.php` |
| History Model | `app/Models/CMW/History/HistoryItemPrice.php` |
| Partner Model | `app/Models/CMW/Master/Partner.php` |
| Company Model | `app/Models/CMW/Master/Company.php` |
| Warehouse Model | `app/Models/CMW/Master/Warehouse.php` |
| Pricing Helper | `app/Helpers/CMW/PriceResolutionHelper.php` |
| Populate Helper | `app/Helpers/CMW/PopulateDataHelper.php` |
| Item Livewire | `app/Livewire/Inventories/Item/` |
| Adjustment Livewire | `app/Livewire/Inventories/Adjustment/` |
| ItemPrice Livewire | `app/Livewire/Inventories/ItemPrice/` |
| CategoryPrice Livewire | `app/Livewire/Inventories/CategoryPrice/` |
| Sales Search Item | `app/Livewire/Sales/Request/Search.php` |
| Cleanup Command | `app/Console/Commands/CleanupItemPriceHistory.php` |
| Migration: items | `database/migrations/2025_12_23_101400_create_items_table.php` |
| Migration: item_warehouses | `database/migrations/2025_12_23_101451_create_item_warehouses_table.php` |
| Migration: company_warehouses | `database/migrations/2025_12_23_101403_create_company_warehouses_table.php` |
| Migration: inventory_ledgers | `database/migrations/2025_12_23_102000_create_inventory_ledgers_table.php` |
