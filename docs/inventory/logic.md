# Inventory Module — Business Logic

**Last Updated**: 2026-02-18

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

## 8. Inventory Ledger (Future Phase)

The `InventoryLedger` model and `inventory_ledgers` table are **structurally ready** but not yet actively posted to by transactions. The design supports:

- **Polymorphic references** (`reference_type` / `reference_id`) to link back to source documents (GR, Sales Delivery, Transfer, Adjustment, Production, etc.)
- **Per-warehouse tracking** with `warehouse_id`
- **Running balance** via `balance` column (always in **base UOM**)
- **Indexed** on `(item_id, warehouse_id, date)` and `(reference_type, reference_id)`

When activated, all stock-affecting transactions must write ledger entries within their DB transaction scope, converting quantities to base UOM using `ItemUom.conversion_rate`.

---

## 9. Database Constraints & Indexes

| Table | Constraint | Type |
|-------|-----------|------|
| `items` | `code` | Unique |
| `item_categories` | `code` | Unique |
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

## 10. Known Limitations & Future Considerations

1. **Ledger not active** — stock quantities are not journaled yet; activation requires writing entries in every stock-affecting transaction
2. **Category price assignment** — currently stored on Partner directly; may need per-company or per-item-category granularity in the future
3. **Price history cleanup** — `cleanup:item-price-history` command retains records for configurable months (default 12), uses `created_at` column
4. **Item.sell_price / cost_price** — serve as last-resort fallback (assumed to be base-UOM prices); should be kept reasonably up to date as a safety net
5. **Multi-UOM price creation** — Item create/edit screens auto-generate price rows for all category prices × all UOMs; standalone ItemPrice CRUD uses `item_uom_id` selector

---

## 11. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Inventory/` |
| ItemUom Model | `app/Models/CMW/Inventory/ItemUom.php` |
| History Model | `app/Models/CMW/History/HistoryItemPrice.php` |
| Partner Model | `app/Models/CMW/Master/Partner.php` |
| Pricing Helper | `app/Helpers/CMW/PriceResolutionHelper.php` |
| Item Livewire | `app/Livewire/Inventories/Item/` |
| ItemPrice Livewire | `app/Livewire/Inventories/ItemPrice/` |
| CategoryPrice Livewire | `app/Livewire/Inventories/CategoryPrice/` |
| Sales Search Item | `app/Livewire/Sales/Request/SearchItem.php` |
| Cleanup Command | `app/Console/Commands/CleanupItemPriceHistory.php` |
| Migrations | `database/migrations/` (item/price/ledger related) |
