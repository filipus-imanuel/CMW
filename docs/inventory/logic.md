# Inventory Module — Business Logic

**Last Updated**: 2026-02-12

---

## 1. Domain Overview

The Inventory module manages **items** (products/materials), their **categorization**, **pricing tiers**, and **stock movements**. It underpins Sales, Purchase, Production, and Warehouse workflows.

### Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Item | `items` | `App\Models\CMW\Inventory\Item` | Master product/material record |
| Item Category | `item_categories` | `App\Models\CMW\Inventory\ItemCategory` | Grouping items (e.g. PP Film, Stretch Film) |
| Category Price | `category_prices` | `App\Models\CMW\Inventory\CategoryPrice` | Pricing tier (e.g. GEN, VIP, DISTRIBUTOR) |
| Item Price | `item_prices` | `App\Models\CMW\Inventory\ItemPrice` | Concrete price per Item × CategoryPrice |
| Pending Item Price | `item_prices_pending` | `App\Models\CMW\Inventory\PendingItemPrice` | Approval workflow for price changes |
| History Item Price | `history_item_prices` | `App\Models\CMW\History\HistoryItemPrice` | Audit log of all price mutations |
| Inventory Ledger | `inventory_ledgers` | `App\Models\CMW\Inventory\InventoryLedger` | Stock movement journal (future phase) |

---

## 2. Relationship Map

```
Partner ──belongsTo──▶ CategoryPrice
    │
    ▼
OrderHeader ──hasMany──▶ OrderDetail ──belongsTo──▶ Item
    │                                                 │
    ├──belongsTo──▶ Company                           ├──belongsTo──▶ ItemCategory
    ├──belongsTo──▶ ItemCategory                      ├──belongsTo──▶ Uom
    └──belongsTo──▶ Partner                           ├──belongsTo──▶ Currency
                                                      ├──hasMany──▶ ItemPrice ──belongsTo──▶ CategoryPrice
                                                      ├──hasMany──▶ HistoryItemPrice
                                                      ├──hasMany──▶ BomHeader
                                                      └──hasMany──▶ InventoryLedger ──belongsTo──▶ Warehouse
                                                                                      └──morphTo──▶ reference

ItemPrice ──hasMany──▶ PendingItemPrice ──belongsTo──▶ User (submittedBy, approvedBy)

ItemCategory ──belongsToMany──▶ Company (pivot: company_item_category)
             ──hasMany──▶ CompanySetting
```

### Cardinality Summary

| From | Relation | To | FK |
|------|----------|----|----|
| Item | N:1 | ItemCategory | `item_category_id` |
| Item | N:1 | Uom | `uom_id` |
| Item | N:1 | Currency | `currency_id` |
| Item | 1:N | ItemPrice | `item_id` |
| Item | 1:N | HistoryItemPrice | `item_id` |
| Item | 1:N | InventoryLedger | `item_id` |
| Item | 1:N | BomHeader | `item_id` |
| ItemPrice | N:1 | CategoryPrice | `category_price_id` |
| ItemPrice | Unique | (item_id, category_price_id) | composite unique |
| PendingItemPrice | N:1 | ItemPrice | `item_price_id` |
| PendingItemPrice | N:1 | Item | `item_id` |
| PendingItemPrice | N:1 | CategoryPrice | `category_price_id` |
| Partner | N:1 | CategoryPrice | `category_price_id` |
| InventoryLedger | N:1 | Warehouse | `warehouse_id` |
| InventoryLedger | morph | reference | `reference_type` + `reference_id` |

---

## 3. Item Type Standards

Items use **UPPER_SNAKE_CASE** type values consistently across UI, database, and seeders:

| Type | Description |
|------|-------------|
| `RAW_MATERIAL` | Raw materials for production |
| `WORK_IN_PROCESS` | Semi-finished goods in production |
| `FINISHED_GOOD` | Completed products ready for sale |
| `SPARE_PART` | Spare parts and consumables |

**Validation rule**: `'required|in:RAW_MATERIAL,WORK_IN_PROCESS,FINISHED_GOOD,SPARE_PART'`

---

## 4. Pricing Resolution (Source of Truth)

### 4.1 Fallback Order

When determining the selling price for an item in a transaction, the system follows this **3-tier fallback**:

```
┌─────────────────────────────────────────────────────┐
│ 1. Customer Category Price                          │
│    Partner.category_price_id → ItemPrice            │
│    (item_id + customer's category_price_id)         │
├──────────────── not found? ─────────────────────────┤
│ 2. General (GEN) Category Price                     │
│    CategoryPrice.code = 'GEN' → ItemPrice           │
│    (item_id + GEN category_price_id)                │
├──────────────── not found? ─────────────────────────┤
│ 3. Item Master Sell Price                           │
│    Item.sell_price                                  │
└─────────────────────────────────────────────────────┘
```

### 4.2 Implementation

- **Helper**: `App\Helpers\CMW\PriceResolutionHelper`
  - `resolve(Item $item, ?Partner $partner)` — single item lookup
  - `resolveMany(Collection $items, ?Partner $partner)` — batch-optimized
- **Return format**: `['price' => float, 'source' => string, 'category_price_id' => int|null]`
- **Sources**: `'customer_category'`, `'general_category'`, `'item_sell_price'`

### 4.3 Usage in Sales Flow

1. User opens **Sales Request → Edit** and clicks "Add Item"
2. `SearchItem` component receives `itemCategoryId` + `partnerId` from the order context
3. Items are searched and prices resolved via `PriceResolutionHelper::resolveMany()`
4. Resolved price is dispatched as `sellPrice` to the Edit component
5. Price populates the order detail row (user can still override manually)

### 4.4 Price Change Workflow

1. **Direct update** — if change % ≤ threshold (`inventory.item_price.threshold_bypass_approval`): updates `ItemPrice` + writes `HistoryItemPrice` immediately
2. **Approval required** — if change % > threshold: creates `PendingItemPrice` with status `pending`
3. **Approval** — authorized user approves/rejects batch; on approve: updates `ItemPrice` + writes `HistoryItemPrice`; self-approval blocked (except Super Admin)

---

## 5. ItemPrice Soft-Delete Policy

### Rule: **Restore, Never Recreate**

The `item_prices` table has a composite unique constraint on `(item_id, category_price_id)`. When a combination is soft-deleted:

- **Creating** the same combination checks `onlyTrashed()` first
- If a trashed record exists → **restore** it and update with new price
- History log records the old price from the restored record
- This preserves the original record ID and audit trail continuity

### Implications

- The unique constraint remains effective across active records
- No orphaned duplicate rows accumulate over time
- Price history stays linked to the same `item_id + category_price_id` pair

---

## 6. Inventory Ledger (Future Phase)

The `InventoryLedger` model and `inventory_ledgers` table are **structurally ready** but not yet actively posted to by transactions. The design supports:

- **Polymorphic references** (`reference_type` / `reference_id`) to link back to source documents (GR, Sales Delivery, Transfer, Adjustment, Production, etc.)
- **Per-warehouse tracking** with `warehouse_id`
- **Running balance** via `balance` column
- **Indexed** on `(item_id, warehouse_id, date)` and `(reference_type, reference_id)`

When activated, all stock-affecting transactions must write ledger entries within their DB transaction scope.

---

## 7. Database Constraints & Indexes

| Table | Constraint | Type |
|-------|-----------|------|
| `items` | `code` | Unique |
| `item_categories` | `code` | Unique |
| `category_prices` | `code` | Unique |
| `item_prices` | `(item_id, category_price_id)` | Unique (composite) |
| `item_prices` | SoftDeletes | Yes |
| `history_item_prices` | `(item_id, category_price_id, created_at)` | Index |
| `item_prices_pending` | `(item_price_id, status)` | Index |
| `item_prices_pending` | `(status, submitted_at)` | Index |
| `inventory_ledgers` | `(item_id, warehouse_id, date)` | Index |
| `inventory_ledgers` | `(reference_type, reference_id)` | Index |

---

## 8. Known Limitations & Future Considerations

1. **Ledger not active** — stock quantities are not journaled yet; activation requires writing entries in every stock-affecting transaction
2. **Category price assignment** — currently stored on Partner directly; may need per-company or per-item-category granularity in the future
3. **Price history cleanup** — `cleanup:item-price-history` command retains records for configurable months (default 12), uses `created_at` column
4. **Item.sell_price** — serves as last-resort fallback; should be kept reasonably up to date as a safety net

---

## 9. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Inventory/` |
| History Model | `app/Models/CMW/History/HistoryItemPrice.php` |
| Partner Model | `app/Models/CMW/Master/Partner.php` |
| Pricing Helper | `app/Helpers/CMW/PriceResolutionHelper.php` |
| Item Livewire | `app/Livewire/Inventories/Item/` |
| ItemPrice Livewire | `app/Livewire/Inventories/ItemPrice/` |
| CategoryPrice Livewire | `app/Livewire/Inventories/CategoryPrice/` |
| Sales Search Item | `app/Livewire/Sales/Request/SearchItem.php` |
| Cleanup Command | `app/Console/Commands/CleanupItemPriceHistory.php` |
| Migrations | `database/migrations/` (item/price/ledger related) |
