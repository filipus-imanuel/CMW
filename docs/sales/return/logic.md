# Sales Return Module – Business Logic

**Last Updated**: 2026-04-01

## Overview

The Sales Return module handles goods returned by customers after delivery. It supports four return types with distinct processing flows, integrates with warehouse receipt, inventory tracking, and AR invoicing.

> **Warehouse-side return receipt logic**: see [Warehouse Return Logic](../../warehouses/return/logic.md)

---

## Table Structure

| Table                      | Purpose                                      |
|----------------------------|----------------------------------------------|
| `return_headers`           | Main return document (generic, discriminated) |
| `return_details`           | Line items per return                        |
| `inventory_damaged_stocks` | Damaged goods received from returns          |

> Tables use `transaction_type` discriminator (default `'SO'`) for future purchasing reuse.

---

## Return Types

| Type             | Code              | Purpose                                              |
|------------------|-------------------|------------------------------------------------------|
| ITEM             | `ITEM`            | Goods returned for redelivery or inclusion in next SO (price 0) |
| ITEM INVOICE     | `ITEM_INVOICE`    | AR balance reduced at approval + goods to warehouse + next SO at original SO price |
| INVOICE RETURN   | `INVOICE_RETURN`  | AR balance reduction + goods returned to warehouse    |
| INVOICE DISCARD  | `INVOICE_DISCARD` | AR balance reduction only, goods discarded (no warehouse) |

---

## Status Flow

```
                    ┌─ ITEM ──────────────► PROCESSING ──► WH Receipt ──► Allocation ──► FINISH
                    │
                    ├─ ITEM_INVOICE ──────► potong invoice + PROCESSING ──► WH Receipt ──► Allocation ──► FINISH
                    │
INIT ──► APPROVAL ──┼─ INVOICE_RETURN ────► PROCESSING ──► WH Receipt (+ potong invoice) ──► FINISH
                    │
                    ├─ INVOICE_DISCARD ──► potong invoice ──► FINISH  (skip warehouse)
                    │
                    ├──reject──► REJECTED   (imposed by approver)
                    │
                    └──cancel──► CANCELLED  (voluntary by user, PROCESSING pre-receipt only)
```

| Status      | Description                                           |
|-------------|-------------------------------------------------------|
| `INIT`      | Draft – editable, deletable                           |
| `APPROVAL`  | Submitted for manager approval                        |
| `PROCESSING`| Approved – awaiting warehouse receipt & allocation    |
| `FINISH`    | Fully processed                                       |
| `CANCELLED` | Voluntarily cancelled by user                         |
| `REJECTED`  | Rejected during approval (reason required)            |

---

## Creation Rules

1. Returns are created **from a finished Delivery Order (DO)**.
2. Each DO can have at most **one active return** (non-CANCELLED, non-REJECTED).
3. Creation can be initiated from:
   - SO Show page → "Create Return" link per finished DO
   - Sales Return Draft index → Create button (cascade selects: customer → SO → DO)
4. On creation, all delivery detail lines are copied with `quantity_return` defaulting to `quantity_delivered`.

---

## Edit (INIT Status Only)

- Adjust `quantity_return` per line (1 ≤ qty ≤ delivery qty).
- Remove individual lines.
- Recalculate line totals via `TransactionHelper::calculateItemTax()`.
- Header totals (subtotal, tax, total) recalculated server-side.
- **Submit** transitions status to `APPROVAL`.

---

## Approval (APPROVAL Status)

| Action  | Result (ITEM)                  | Result (ITEM_INVOICE)                          | Result (INVOICE_RETURN)              | Result (INVOICE_DISCARD)               | Required Permission     |
|---------|--------------------------------|------------------------------------------------|--------------------------------------|----------------------------------------|-------------------------|
| Approve | → `PROCESSING`                 | → `PROCESSING` (AR balance reduced at approval)| → `PROCESSING`                       | → `FINISH` (AR balance reduced)        | `approve sales return`  |
| Reject  | → `REJECTED` + reason          | → `REJECTED` + reason                          | → `REJECTED` + reason                | → `REJECTED` + reason                  | `reject sales return`   |

> **INVOICE_DISCARD**: On approval the AR invoice balance is reduced immediately and the return is completed. No warehouse receipt or inventory records are created.

> **ITEM_INVOICE**: On approval the AR invoice balance is reduced, then the return proceeds to warehouse for receipt and allocation (same flow as ITEM).

---

## Warehouse Receipt (PROCESSING Status – ITEM, ITEM_INVOICE & INVOICE_RETURN Only)

Handled by Warehouse module (`Warehouses\Return\Show`). See [Warehouse Return Logic](../../warehouses/return/logic.md) for full receipt details.

> **Note**: `INVOICE_DISCARD` returns never reach PROCESSING status and are not visible to the warehouse.

**Summary**: Warehouse splits returned goods into good/damaged per line, selects receiving warehouse, and confirms receipt. Good stock creates `InventoryLedger` entries; damaged stock creates `InventoryDamagedStock` records. For `INVOICE_RETURN`, AR balance is reduced and return status → `FINISH`. For `ITEM` and `ITEM_INVOICE`, return stays `PROCESSING` awaiting allocation.

---

## Item Allocation (PROCESSING + Post-Receipt, ITEM & ITEM_INVOICE Types)

Handled by Sales Return Show page.

### Per Line Allocation
| Field               | Constraint                                          |
|---------------------|-----------------------------------------------------|
| `quantity_redelivery` | ≥ 0                                               |
| `quantity_next_so`    | ≥ 0                                               |
| **Sum**             | Must not exceed `quantity_return`                   |

### On Process Allocation

**Validation**:
1. `quantity_redelivery + quantity_next_so ≤ quantity_return` per line
2. No negative quantities
3. Redelivery: warehouse stock check — `TransactionHelper::getWarehouseBalance()` must have sufficient base-UOM stock for redelivery quantity

**Processing** (inside `DB::transaction`):
1. Updates each `ReturnDetail` with `quantity_redelivery`, `quantity_next_so`
2. **Redelivery > 0**: Reverts linked SO status from `FINISH` to `DELIVERY` so a new DO can be created
3. **Next SO > 0**: Items become available for consumption in a future Sales Request
4. Status → `FINISH`
5. Dispatches `shp.sales.return.finished`
6. **Redirect**: `sales.return.index.ongoing`

### Cancel (PROCESSING pre-receipt)

- Permission: `edit sales return`
- Only allowed when `isProcessing() && !isWarehouseReceived()`
- Sets status → `CANCELLED`
- Dispatches `shp.sales.return.cancelled`
- **Redirect**: `sales.return.index.ongoing`

---

## Next SO Integration (Sales Request Create & Edit)

Available on both Create and Edit pages. Return items are filtered at query level by:
- Same `partner_id`
- Same `company_id` (on `return_headers`)
- Same `item_category_id` (on original SO)
- Same `tax_mode` + `tax_id` (on original SO)
- Return status = `FINISH`, type = `ITEM` or `ITEM_INVOICE`
- `quantity_next_so > 0`, `is_next_so_consumed = false`

### Price Behavior
- **ITEM** type: `price = 0` (free replacement)
- **ITEM_INVOICE** type: `price = original SO price` (from `return_details.price`)

### On Create
Selected return items create `OrderDetail` rows directly in the DB transaction with `return_detail_id` FK set. Price is set based on return type (0 for ITEM, original SO price for ITEM_INVOICE).

### On Edit
Existing return-origin items loaded from `order_details.return_detail_id`. Additional items can be toggled. Return-origin items display with "↩" prefix.

### On SR Submit
- `ReturnDetail` rows linked via `order_details.return_detail_id` are marked:
  - `is_next_so_consumed = true`
  - `consumed_by_order_id` = this order's ID

---

## Permission Matrix

### Sales Group
| Permission            | Roles          |
|-----------------------|----------------|
| `view sales return`   | Sales, Admin   |
| `create sales return` | Sales, Admin   |
| `edit sales return`   | Sales, Admin   |
| `delete sales return` | Sales, Admin   |
| `approve sales return`| Sales, Admin   |
| `reject sales return` | Sales, Admin   |

> Warehouse-side permissions: see [Warehouse Return Logic](../../warehouses/return/logic.md).

---

## Code Generation

Format: `RTN/YYMM/NNNN`

- Prefix: `RTN`
- Year-month: 2-digit year + 2-digit month
- Sequence: 4-digit zero-padded, per month, uses `withTrashed()`

---

## Event Naming Convention

Pattern: `shp.{module}.{entity}.{action}`

| Event | Dispatched When |
|-------|----------------|
| `sales.return.refresh.draft` | Return created (Create.php) |
| `shp.sales.return.submitted` | Return submitted for approval (Edit.php) |
| `shp.sales.return.approved` | Return approved → PROCESSING (Show.php, ITEM/ITEM_INVOICE/INVOICE_RETURN) |
| `shp.sales.return.finished` | Return completed → FINISH (Show.php, INVOICE_DISCARD at approval or allocation complete) |
| `shp.sales.return.refresh.rejected` | Return rejected (Show.php) |
| `shp.sales.return.cancelled` | Return cancelled from PROCESSING (Show.php) |

### DataTable Listeners

| Listener | DataTable | Notes |
|----------|-----------|-------|
| `sales.return.refresh.draft` | DraftDataTable | Matched by Create.php |
| `sales.return.refresh.approval` | ApprovalDataTable | No dispatch currently matches |
| `sales.return.refresh.ongoing` | OngoingDataTable | No dispatch currently matches |
| `sales.return.refresh.finish` | FinishDataTable | No dispatch currently matches |
| `shp.sales.return.refresh.rejected` | RejectedDataTable | Matched by Show.php reject |
| `sales.return.refresh.cancelled` | CancelledDataTable | No dispatch currently matches |
| `shp.sales.return.cancelled` | — | Dispatched but no DataTable listener |
| `shp.warehouse.return.received` | — | Dispatched by Warehouse Return Show |

---

## Route Structure

### Sales Return (`/sales/returns/...`)
| Route Name                      | Path                        | Component                        |
|---------------------------------|-----------------------------|----------------------------------|
| `sales.return.index.draft`      | `/`                         | `Sales\Return\Index\Draft`       |
| `sales.return.index.approval`   | `/approval`                 | `Sales\Return\Index\Approval`    |
| `sales.return.index.ongoing`    | `/ongoing`                  | `Sales\Return\Index\Ongoing`     |
| `sales.return.index.finish`     | `/finish`                   | `Sales\Return\Index\Finish`      |
| `sales.return.index.cancelled`  | `/cancelled`                | `Sales\Return\Index\Cancelled`   |
| `sales.return.index.rejected`   | `/rejected`                 | `Sales\Return\Index\Rejected`    |
| `sales.return.create`           | `/create/{deliveryId?}`     | `Sales\Return\Create`            |
| `sales.return.edit`             | `/{id}/edit`                | `Sales\Return\Edit`              |
| `sales.return.show`             | `/{id}`                     | `Sales\Return\Show`              |

### Warehouse Return (`/warehouses/return/...`)
| Route Name              | Path     | Component                    |
|-------------------------|----------|------------------------------|
| `warehouses.return.index`| `/`     | `Warehouses\Return\Index`    |
| `warehouses.return.show` | `/{id}` | `Warehouses\Return\Show`     |

---

## Navigation

- **Sales sidebar**: Return sub-group (expandable) with Draft, Approval, Ongoing, Finish, Cancelled, Rejected
- **Warehouse sidebar**: Return item after Cancelled Delivery
- **SO Show page**: "Create Return" action per finished DO, Returns card listing all linked returns
- **SR Edit page**: "Return Items Available" card with selectable return items
