# Stock Adjustment — Business Logic

**Module**: `Inventories / Adjustment`
**Last Updated**: 2026-03-15

---

## 1. Purpose

Stock Adjustment reconciles **physical stock** against **system (ledger) stock** for a specific warehouse. When confirmed, it creates `InventoryLedger` entries to align system balances with reality. When cancelled after confirmation, it creates reversal ledger entries.

---

## 2. Status Flow

```
DRAFT → CONFIRMED
  ↓         ↓
  → CANCELLED ←
```

| Constant | Value | Editable | Actions Available |
|----------|-------|----------|-------------------|
| `STATUS_DRAFT` | `draft` | Yes | Edit, Confirm, Cancel |
| `STATUS_CONFIRMED` | `confirmed` | No (locked) | Cancel (with reversal) |
| `STATUS_CANCELLED` | `cancelled` | No (locked) | None |

On confirm or cancel: `is_edit_locked = true`, `is_delete_locked = true`.

---

## 3. Data Model

### 3.1 StockAdjustmentHeader

| Field | Type | Notes |
|-------|------|-------|
| `code` | string | Auto-generated via `CodeGeneratorHelper::generateAdjustmentCode()` |
| `date` | date | Adjustment date (used as ledger entry date on confirm) |
| `currency_id` | FK → currencies | Currency reference |
| `warehouse_id` | FK → warehouses | Target warehouse |
| `order_header_id` | FK → order_headers, nullable | Optional link to a Sales Order (WO traceability) |
| `work_order_auto` | string(50), nullable | Snapshot of linked SO's auto WO at time of save |
| `work_order_manual` | string(100), nullable | Manual WO; kept in sync via cascade from `Sales\Order\Show::saveWorkOrderManual()` |
| `production_date` | date, nullable | Production target date; kept in sync via cascade from `Sales\Order\Show::saveWorkOrderManual()` |
| `status` | string | `draft` / `confirmed` / `cancelled` |
| `remarks` | text | Optional |
| `is_edit_locked` | boolean | Locked after confirm/cancel |
| `is_delete_locked` | boolean | Locked after confirm/cancel |

**Relationships**: `warehouse()`, `currency()`, `orderHeader()`, `details()`, `inventoryLedgers()` (morphMany)

### 3.2 StockAdjustmentDetail

| Field | Type | Notes |
|-------|------|-------|
| `stock_adjustment_header_id` | FK | Parent header |
| `item_id` | FK → items | Adjusted item |
| `item_uom_id` | FK → item_uoms | Selected UOM for display |
| `warehouse_id` | FK → warehouses | Same as header warehouse |
| `quantity_system` | decimal(2) | System balance at time of creation (in selected UOM) |
| `quantity_actual` | decimal(2) | Physical count entered by user (in selected UOM) |
| `quantity_difference` | decimal(2) | `actual - system` (in selected UOM) |
| `remarks` | text | Per-line notes |

**Relationships**: `header()`, `item()`, `itemUom()`, `warehouse()`

---

## 4. CRUD Operations

### 4.1 Create (`Create.php`)

- **Permission**: `create stock adjustment`
- **Header fields**: `$inputs[]` pattern — `date`, `warehouse_id`, `order_header_id`, `work_order_auto`, `work_order_manual`, `production_date`, `remarks`
- **Lines**: dynamic array — `item_id`, `item_uom_id`, `quantity_actual`, `remarks`
- **System qty auto-fetch**: when `item_id` or `item_uom_id` changes, `fetchSystemQuantity()` queries ledger for latest balance (base UOM) and converts to selected UOM
- **Warehouse filter**: changing `warehouse_id` filters `dropdown_items` to only items assigned to that warehouse via `PopulateDataHelper::getItemsByWarehouse()`
- **Difference calc**: `quantity_difference = quantity_actual - quantity_system` (client-side recalc on change)
- **SO / Work Order link (optional)**:
  - `soFilter` tri-state: `has_wo` (default) / `no_wo` / `all`
  - `soDateFrom` / `soDateTo` (default: last 30 days) constrain by `order_headers.date`
  - `soSearch` is a debounced (300ms), server-side LIKE across `code_order`, `code_request`, `work_order_auto`, `work_order_manual`
  - `orderOptions` is a `#[Computed]` property returning at most 20 rows (status ∈ `ORDER`, `DELIVERY`, `FINISH`, `FINAL`) plus the currently selected SO pinned
  - View renders `flux:select` as `variant="combobox"` with `:filter="false"` and a `<x-slot name="input">` wired to `soSearch`
  - Selecting an SO pre-fills `work_order_auto` (read-only), `work_order_manual` (editable), and `production_date` (editable) via `updatedInputsOrderHeaderId()`
  - Index `(status, date)` on `order_headers` keeps the lookup fast even on large datasets
  - **Preselect via query param**: navigating with `?order_header_id={id}` (e.g. from the Production "Create Adjustment" shortcut) auto-selects the SO on mount, after validating the ID belongs to a SO in `ORDER`/`DELIVERY`/`FINISH`/`FINAL` status
- **Store**: Creates header (status=DRAFT) + detail lines in a DB transaction, then redirects to Show

### 4.2 Edit (`Edit.php`)

- **Permission**: `edit stock adjustment`
- **Status guard**: only DRAFT can be edited; redirects to Show if confirmed/cancelled
- **Lock guard**: checks `is_edit_locked`
- **Populate**: loads existing header + details into `$inputs[]` and `$lines[]` (including `order_header_id`, `work_order_auto`, `work_order_manual`, `production_date`)
- **SO combobox**: same server-side / debounced behavior as Create; the originally-linked SO is also pinned so its label persists across filter/search changes
- **Update**: in DB transaction — updates header (including SO/WO fields), upserts detail lines, soft-deletes removed lines (`deleted_by` set + `delete()`)

### 4.3 Show (`Show.php`)

- **Permission**: `view stock adjustment`
- **Eager loads**: `warehouse`, `orderHeader`, `details.item`, `details.itemUom.uom`, `createdBy`, `updatedBy`, `inventoryLedgers`
- Displays linked SO code, `work_order_auto`, `work_order_manual`, and `production_date` (read-only); `work_order_manual` and `production_date` reflect the latest values cascaded from the SO
- **Actions**: Confirm (draft only), Cancel (draft or confirmed)

### 4.4 Index / DataTable (`IndexDataTable.php`)

- **Listener**: `cmw.inventories.stock-adjustment.refresh` → `$refresh`
- **Filters**: Status (draft/confirmed/cancelled)
- **Columns**: Actions, Code, Date, Warehouse, Status, Remarks, Created By
- **Actions column**: uses `components.datatables.datatable-action` — Show (always), Edit (draft only), Delete (disabled)

---

## 5. Confirm Logic (`Show::confirm()`)

**Permission**: `confirm stock adjustment`

**Precondition**: header must be DRAFT.

**Process** (inside `DB::transaction`):

1. `$this->header->lockForUpdate()` — prevent concurrent modifications
2. For each detail where `quantity_difference ≠ 0`:
   a. Convert `quantity_difference` from selected UOM to base UOM via `TransactionHelper::convertToBaseUom()`
   b. Get current ledger balance for `(item_id, warehouse_id)` — always in base UOM
   c. Calculate `newBalance = currentBalance + baseDifference`
   d. Create `InventoryLedger` entry:
      - `type` = `'adjustment'`
      - `reference_type` = `StockAdjustmentHeader::class` (morphMany)
      - `reference_id` = header ID
      - `quantity_in` = positive difference (stock increase)
      - `quantity_out` = negative difference (stock decrease)
      - `balance` = new running balance
      - `unit_cost` = `Item.cost_price`
      - `date` = header date
      - `remarks` = `'Stock Adjustment: {code} - {line remarks}'`
3. Update header: `status = confirmed`, lock edit + delete
4. Reload model with all relations
5. Dispatch `shp.inventories.stock-adjustment.confirmed`

---

## 6. Cancel Logic (`Show::cancel()`)

**Permission**: `cancel stock adjustment`

**Precondition**: header must NOT already be cancelled.

**Draft cancel**: simply marks as cancelled (no ledger entries to reverse).

**Confirmed cancel** (inside `DB::transaction`):

1. `$this->header->lockForUpdate()`
2. For each detail where `quantity_difference ≠ 0`:
   a. Convert difference to base UOM via `TransactionHelper::convertToBaseUom()`
   b. Get current ledger balance
   c. Create **reversal** ledger entry: `newBalance = currentBalance - baseDifference`
      - Reversal flips `quantity_in`/`quantity_out` vs. original
      - `date` = `now()` (not original header date)
      - `remarks` = `'REVERSAL - Stock Adjustment: {code}'`
3. Update header: `status = cancelled`, lock edit + delete
4. Dispatch `shp.inventories.stock-adjustment.cancelled`

---

## 7. UOM Conversion

### System Quantity Display

Ledger balance is always in **base UOM**. For display in the selected UOM:
```
displayQty = baseBalance / conversionRate
```

### Confirm/Cancel (Ledger Write)

Difference is stored in selected UOM. For ledger write, convert back to base:
```
baseDifference = TransactionHelper::convertToBaseUom(difference, itemUomId)
```

Where `convertToBaseUom()` multiplies by the conversion rate.

---

## 8. Validation Rules

| Field | Rule |
|-------|------|
| `inputs.date` | `required\|date` |
| `inputs.warehouse_id` | `required\|exists:warehouses,id` |
| `inputs.remarks` | `nullable\|string\|max:1024` |
| `lines` | `required\|array\|min:1` |
| `lines.*.item_id` | `required\|exists:items,id` |
| `lines.*.item_uom_id` | `required\|exists:item_uoms,id` |
| `lines.*.quantity_actual` | `required\|numeric\|min:0` |
| `lines.*.remarks` | `nullable\|string\|max:1024` |

---

## 9. Permissions

| Permission | Used In |
|------------|---------|
| `view stock adjustment` | Index, Show |
| `create stock adjustment` | Create |
| `edit stock adjustment` | Edit, DataTable edit button |
| `confirm stock adjustment` | Show confirm action |
| `cancel stock adjustment` | Show cancel action |

---

## 10. Routes

Prefix: `/cmw/inventories/stock-adjustments/` — Name: `inventories.stock-adjustments.*`

| Method | URI | Component | Route Name |
|--------|-----|-----------|------------|
| GET | `/` | `Index` | `.index` |
| GET | `/create` | `Create` | `.create` |
| GET | `/{id}/edit` | `Edit` | `.edit` |
| GET | `/{id}` | `Show` | `.show` |

---

## 11. Events

| Event | Dispatched By | Listener |
|-------|---------------|----------|
| `shp.inventories.stock-adjustment.confirmed` | `Show::confirm()` | — |
| `shp.inventories.stock-adjustment.cancelled` | `Show::cancel()` | — |
| `cmw.inventories.stock-adjustment.refresh` | — | `IndexDataTable` |

---

## 12. Related Files

| Area | Path |
|------|------|
| Header Model | `app/Models/CMW/Transaction/StockAdjustmentHeader.php` |
| Detail Model | `app/Models/CMW/Transaction/StockAdjustmentDetail.php` |
| Create | `app/Livewire/Inventories/Adjustment/Create.php` |
| Edit | `app/Livewire/Inventories/Adjustment/Edit.php` |
| Show | `app/Livewire/Inventories/Adjustment/Show.php` |
| Index | `app/Livewire/Inventories/Adjustment/Index.php` |
| DataTable | `app/Livewire/Inventories/Adjustment/IndexDataTable.php` |
| Code Generator | `app/Helpers/CMW/CodeGeneratorHelper.php` |
| Transaction Helper | `app/Helpers/CMW/TransactionHelper.php` |
| Populate Helper | `app/Helpers/CMW/PopulateDataHelper.php` |
