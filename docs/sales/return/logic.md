# Sales Return Module – Business Logic

## Overview

The Sales Return module handles goods returned by customers after delivery. It supports three return types with distinct processing flows, integrates with warehouse receipt, inventory tracking, and AR invoicing.

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
| ITEM             | `ITEM`            | Goods returned for redelivery or inclusion in next SO |
| INVOICE RETURN   | `INVOICE_RETURN`  | AR balance reduction + goods returned to warehouse    |
| INVOICE DISCARD  | `INVOICE_DISCARD` | AR balance reduction only, goods discarded (no warehouse) |

---

## Status Flow

```
                    ┌─ ITEM ──────────────► PROCESSING ──► WH Receipt ──► Allocation ──► FINISH
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

| Action  | Result (ITEM / INVOICE_RETURN)       | Result (INVOICE_DISCARD)               | Required Permission     |
|---------|--------------------------------------|----------------------------------------|-------------------------|
| Approve | → `PROCESSING`                       | → `FINISH` (AR balance reduced)        | `approve sales return`  |
| Reject  | → `REJECTED` + reason                | → `REJECTED` + reason                  | `reject sales return`   |

> **INVOICE_DISCARD**: On approval the AR invoice balance is reduced immediately and the return is completed. No warehouse receipt or inventory records are created.

---

## Warehouse Receipt (PROCESSING Status – ITEM & INVOICE_RETURN Only)

Handled by Warehouse module (`Warehouses\Return\Show`).

> **Note**: `INVOICE_DISCARD` returns never reach PROCESSING status and are not visible to the warehouse.

### Input per Line
| Field                     | Default       | Constraint                           |
|---------------------------|---------------|--------------------------------------|
| `quantity_received_good`  | qty_return    | ≥ 0                                  |
| `quantity_received_damaged`| 0            | ≥ 0                                  |
| **Sum**                   |               | Must equal `quantity_return`          |

### On Confirm Receipt

1. **Good stock**: `InventoryLedger` entry (credit/in) linked via morphMany.
2. **Damaged stock**: `InventoryLedger` entry + `InventoryDamagedStock` record with morph reference to `ReturnDetail`.
3. **INVOICE_RETURN type**: Reduce AR invoice balance by return total → status → `FINISH`.
4. **ITEM type**: Remains `PROCESSING` (awaiting allocation on Sales side).
5. Sets `received_by` and `received_at` on header.

---

## Item Allocation (PROCESSING + Post-Receipt, ITEM Type Only)

Handled by Sales Return Show page.

### Per Line Allocation
| Field               | Constraint                                          |
|---------------------|-----------------------------------------------------|
| `quantity_redelivery` | ≥ 0                                               |
| `quantity_next_so`    | ≥ 0                                               |
| **Sum**             | Must equal `quantity_received_good`                 |

### On Process Allocation

1. **Redelivery > 0**: Reverts linked SO status to `PROCESSING` so a new DO can be created.
2. **Next SO > 0**: Items become available for consumption in a future Sales Request.
3. Status → `FINISH`.

---

## Next SO Integration (Sales Request Create & Edit)

Available on both Create and Edit pages. Return items are filtered at query level by:
- Same `partner_id`
- Same `company_id` (on `return_headers`)
- Same `item_category_id` (on original SO)
- Same `tax_mode` + `tax_id` (on original SO)
- Return status = `FINISH`, type = `ITEM`
- `quantity_next_so > 0`, `is_next_so_consumed = false`

### On Create
Selected return items create `OrderDetail` rows directly in the DB transaction with `return_detail_id` FK set, price = 0.

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

### Warehouse Group
| Permission              | Roles              |
|--------------------------|-------------------|
| `view warehouse return`  | Warehouse, Admin  |
| `receive warehouse return`| Warehouse, Admin |

---

## Code Generation

Format: `RTN/YYMM/NNNN`

- Prefix: `RTN`
- Year-month: 2-digit year + 2-digit month
- Sequence: 4-digit zero-padded, per month, uses `withTrashed()`

---

## Event Naming Convention

Pattern: `shp.{module}.{entity}.{action}`

Examples:
- `shp.sales.return.created`
- `shp.sales.return.approved`
- `shp.sales.return.finished`
- `shp.sales.return.refresh.rejected`
- `shp.sales.return.refresh.cancelled`
- `shp.warehouse.return.received`

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
