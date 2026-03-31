# Warehouse Return Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The Warehouse Return module handles the **physical receipt of returned goods** from customers. It is the warehouse-side counterpart to the Sales Return module. Warehouse staff process the receipt by splitting returned quantities into good stock and damaged stock, then writing inventory ledger entries.

> For sales-side return logic (creation, approval, allocation), see [Sales Return Logic](../../sales/return/logic.md).

---

## 2. Scope

This module only handles returns with status `PROCESSING` that have **not yet been received** by the warehouse. The following return types reach the warehouse:

| Return Type | Reaches Warehouse? | Post-Receipt Behavior |
|-------------|--------------------|-----------------------|
| `ITEM` | Yes | Stays `PROCESSING` → Sales allocates → `FINISH` |
| `ITEM_INVOICE` | Yes | Stays `PROCESSING` → Sales allocates → `FINISH` |
| `INVOICE_RETURN` | Yes | AR balance reduced + status → `FINISH` |
| `INVOICE_DISCARD` | **No** | Completes at approval, never reaches warehouse |

---

## 3. Components

| Component | Class | Purpose |
|-----------|-------|---------|
| Index | `Warehouses\Return\Index` | List of returns pending warehouse receipt |
| IndexDataTable | `Warehouses\Return\IndexDataTable` | Rappasoft DataTable for pending returns |
| Show | `Warehouses\Return\Show` | Receipt form: split good/damaged, select warehouse |

---

## 4. Index (Pending Returns List)

- **Permission**: `view warehouse return`
- **Scope**: `ReturnHeader::salesOrder()->warehousePending()` — only `PROCESSING` status returns without warehouse receipt
- **Columns**: Actions, RTN Code, Date, Customer, DO Code, Return Type (badge), Total, Created By
- **Listener**: `warehouse.return.refresh` → `$refresh`

---

## 5. Show (Receipt Form)

### 5.1 Access & Guards

- **Permission**: `view warehouse return` (mount), `receive warehouse return` (confirmReceipt)
- **Status guard**: Only `PROCESSING` status + not yet received (`isWarehouseReceived() === false`)
- **Redirect**: Invalid status → back to index with toast

### 5.2 Line Item Fields

| Field | Default | Constraint |
|-------|---------|------------|
| `quantity_received_good` | `quantity_return` (all good) | ≥ 0 |
| `quantity_received_damaged` | `0` | ≥ 0 |
| `warehouse_id` | Original DO detail's `warehouse_id` | Required (dropdown of active warehouses) |
| **Sum** | | `quantity_received_good + quantity_received_damaged == quantity_return` |

### 5.3 Auto-Sync Behavior

When user changes `quantity_received_good`, the system auto-calculates `quantity_received_damaged = quantity_return - quantity_received_good` (and vice versa). Values are clamped to `[0, quantity_return]`.

### 5.4 Confirm Receipt (`confirmReceipt()`)

Authorization: `receive warehouse return`

**Validation** (per line):
1. Good + Damaged must equal `quantity_return` (tolerance: 0.01)
2. `warehouse_id` must be set

**Processing** (inside `DB::transaction`):

For each line:

1. **Update ReturnDetail**: set `quantity_received_good`, `quantity_received_damaged`

2. **Good stock** (`quantity_received_good > 0`):
   - Convert to base UOM: `baseQty = qty × conversionRate`
   - Get current balance: last `InventoryLedger` row for `(item_id, warehouse_id)` ordered by `date DESC, id DESC`
   - Create `InventoryLedger` entry:
     - `type = 'sales_return'`
     - `quantity_in = baseGoodQty`
     - `balance = currentBalance + baseGoodQty`
     - `reference_type = ReturnHeader::class`
     - `reference_id = returnHeader.id`

3. **Damaged stock** (`quantity_received_damaged > 0`):
   - Create `InventoryDamagedStock` entry:
     - `reference_type = ReturnDetail::class`
     - `reference_id = detail.id`
     - Quantity stored in **transaction UOM** (not converted to base)

**Post-line processing**:

4. **Update header**: set `received_by`, `received_at`

5. **INVOICE_RETURN type only**: call `processInvoiceReturn()`:
   - Reduce AR invoice balance: `newReturnTotal = arInvoice.return_total + returnHeader.total`
   - Recalculate: `newBalance = total - paid - newReturnTotal`
   - Update AR invoice `return_total`, `balance`, `status` (→ `paid` if balance ≤ 0, else `partial`)
   - Set return status → `FINISH`

6. **ITEM / ITEM_INVOICE type**: stays `PROCESSING` — awaits Sales-side allocation

7. **Dispatch event**: `shp.warehouse.return.received`

8. **Redirect**: back to index

---

## 6. Permission Matrix

| Permission | Action | Default Roles |
|------------|--------|---------------|
| `view warehouse return` | View pending returns index & details | Warehouse |
| `receive warehouse return` | Confirm receipt of goods | Warehouse |

---

## 7. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `warehouses.return.index` | `/cmw/warehouses/return` | `Warehouses\Return\Index` |
| `warehouses.return.show` | `/cmw/warehouses/return/{id}` | `Warehouses\Return\Show` |

---

## 8. Events

| Event | Dispatched When |
|-------|----------------|
| `shp.warehouse.return.received` | Warehouse receipt confirmed |
| `warehouse.return.refresh` | Listener on IndexDataTable for list refresh |

---

## 9. Related Files

| Area | Path |
|------|------|
| Components | `app/Livewire/Warehouses/Return/Index.php`, `IndexDataTable.php`, `Show.php` |
| Models | `app/Models/CMW/Transaction/ReturnHeader.php`, `ReturnDetail.php` |
| Inventory Models | `app/Models/CMW/Inventory/InventoryLedger.php`, `InventoryDamagedStock.php` |
| AR Invoice Model | `app/Models/CMW/Transaction/ArInvoiceHeader.php` |
