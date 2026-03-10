# Sales Module — Business Logic

**Last Updated**: 2026-03-10

---

## 1. Domain Overview

The Sales module manages the lifecycle of sales from **request → approval → order**. A single `OrderHeader` record progresses through statuses; no parent-child duplication.

### Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Order Header | `order_headers` | `OrderHeader` | Sales request / order header |
| Order Detail | `order_details` | `OrderDetail` | Line items with pricing |

### Status Flow

```
INIT ──submit──▶ APPROVAL ──approve──▶ ORDER ──▶ DELIVERY ──▶ FINISH ──▶ FINAL
  │                 │
  │                 ├──reject──▶ REJECTED
  │                 │
  │                 └──restore──▶ INIT
  │
  └──cancel──▶ CANCELLED  (also from ORDER if no deliveries)
```

| Status | Meaning | Editable | Scope |
|--------|---------|----------|-------|
| `INIT` | Draft request | Yes | `scopeInit` |
| `APPROVAL` | Pending approval | No | `scopePendingApproval` |
| `ORDER` | Approved, ongoing | No | `scopeOngoing` |
| `REJECTED` | Rejected by approver (imposed) | No | `scopeRejected` |
| `CANCELLED` | Cancelled by user (voluntary) | No | `scopeCancelled` |
| `DELIVERY`+ | Future statuses | No | `scopeOrders` |

---

## 2. Dual Code System

Each order has two code columns:

| Column | Prefix | Generated When | Example |
|--------|--------|----------------|---------|
| `code_request` | `SR` | On create (INIT) | `SR/2602/0001` |
| `code_order` | `SO` | On approve (→ORDER) | `SO/2602/0001` |

`CodeGeneratorHelper::generateOrderCode($prefix)` determines which column to search based on prefix (`SR` → `code_request`, `SO` → `code_order`).

---

## 3. Pricing Columns

Each `OrderDetail` has two price fields:

| Column | Set By | Purpose |
|--------|--------|---------|
| `price_proposed` | Salesperson (from PriceResolutionHelper) | Proposed selling price |
| `price_deal` | Salesperson (before submit) | Final agreed price |

- `price_proposed` populates automatically from `PriceResolutionHelper` when adding items
- `price_deal` must be filled before submit; defaults to `0`
- `TransactionHelper::updateOrderTotals()` calculates using `price_proposed`

---

## 4. Sales Request (SR) Flow

### 4.1 Create (`Sales\Request\Create`)

1. Permission: `create sales request`
2. Generates `code_request` via `CodeGeneratorHelper::generateOrderCode('SR')`
3. Sets status `INIT`
4. **Selection order**: Company → Customer → Item Category. Selecting a company filters both the customer dropdown (only customers linked to that company via `company_partner` pivot) and the item category dropdown (only categories linked to that company via `company_item_category`). Non-Super Admin users also have the customer list filtered by `user_id` (PIC).
5. **Return items**: shows checklist of available return items (see §4.4). Selected items create `OrderDetail` rows directly inside the creation transaction with `return_detail_id` set, `price = 0`.
6. Redirects to Edit

### 4.2 Edit (`Sales\Request\Edit`)

1. Permission: `edit sales request`
2. Only `INIT` status orders are editable
3. Fields: header info + `delivery_date` + line items
4. Items added via `SearchItem` modal (dispatches resolved price → `price_proposed`)
5. Price override requires `override price sales request` permission
6. Tax override requires `override tax sales request` permission; without it tax fields are disabled
7. Return-origin items (linked via `return_detail_id`) display with "↩" prefix
8. Additional return items can be toggled from the "Return Items Available" card

### 4.3 Submit (INIT → APPROVAL)

Validation before submit:
- At least 1 detail item
- `delivery_date` required
- Non-return items must have `price_deal > 0` (return items allowed at 0)

On submit:
- Status → `APPROVAL`
- Return consumption: `ReturnDetail` rows linked via `order_details.return_detail_id` are marked `is_next_so_consumed = true`, `consumed_by_order_id` = this order
- Dispatches `shp.sales.order.refresh.approval`

### 4.4 Return Items Integration

Both Create and Edit show available return items filtered by **all** of:
- Same `partner_id`
- Same `company_id` (on `return_headers`)
- Same `item_category_id` (on original SO `order_headers`)
- Same `tax_mode` + `tax_id` (on original SO `order_headers`)
- Return status = `FINISH`, type = `ITEM`
- `quantity_next_so > 0`, `is_next_so_consumed = false`

`order_details.return_detail_id` (nullable FK → `return_details`) tracks which order detail originated from a return.

---

## 5. SO Approval Flow (`Sales\Approval`)

### 5.1 Index

- Permission: `view sales order` OR `approve sales order`
- Shows `pendingApproval` scope (status = `APPROVAL`)
- Columns: Actions, Code, Date, Delivery Date, Customer, Category, Total, Requested By

### 5.2 Show — Three Actions

| Action | Permission | Result | Events |
|--------|-----------|--------|--------|
| **Restore** | `approve sales order` | → `INIT` | refresh approval + init |
| **Reject** | `reject sales order` | → `REJECTED` (reason required) | refresh approval + rejected |
| **Approve** | `approve sales order` | → `ORDER` + generate `code_order` | refresh approval + ongoing |

- `CustomerCheckHelper` info cards shown (credit limit, pending deliveries, company sales limit)
- Approve generates `code_order` via `CodeGeneratorHelper::generateOrderCode('SO')`
- Approve records `approved_by`, `approved_at`, clears `rejection_reason`

---

## 6. Order Views (`Sales\Order`)

### 6.1 Ongoing Orders

- Permission: `view sales order`
- Scope: `ongoing` (status = `ORDER`)
- Columns: SO Code, SR Code, Date, Delivery Date, Customer, Category, Total, Approved By

### 6.2 Rejected Orders

- Permission: `view sales order`
- Scope: `rejected` (status = `REJECTED`)
- Columns: Code, Date, Delivery Date, Customer, Category, Total, Rejection Reason, Requested By

### 6.3 Show (Detail View)

- Read-only detail for all non-INIT statuses (ORDER, DELIVERY, FINISH, FINAL, REJECTED, CANCELLED)
- Back navigation: INIT → SR init index, REJECTED → rejected index, CANCELLED → cancelled index, else → ongoing index
- Shows both codes, approval info
- **Rejection callout** (REJECTED): displays `rejection_reason` with `x-circle` icon
- **Cancellation callout** (CANCELLED): displays `rejection_reason` (labelled "Cancellation Reason") with `no-symbol` icon
- **Cancel** button (INIT or ORDER + no deliveries, requires `edit sales order`):
  - Opens modal with required cancellation reason textarea
  - Sets status `CANCELLED`, stores reason in `rejection_reason`
  - Dispatches `shp.sales.order.refresh.cancelled`, redirects to cancelled index
- **Replicate** button (REJECTED or CANCELLED, requires `create sales request`):
  - Creates new `INIT` OrderHeader with fresh `code_request`
  - Copies details with `price_proposed = source.price_deal` (or `price_proposed` if no deal)
  - Redirects to Edit of new record

### 6.4 Cancelled Orders

- Permission: `view sales order`
- Scope: `cancelled` (status = `CANCELLED`)
- Columns: Actions, Code, Date, Customer, Category, Total, Cancellation Reason, Requested By

---

## 7. CustomerCheckHelper

Runs on approval page. Returns array with keys:

| Check | Key | Flags |
|-------|-----|-------|
| Credit limit | `debt` | `exceeded`, `projected`, `limit`, `remaining`, `outstanding`, `pending_orders` |
| Pending deliveries | `deliveries` | Count of pending delivery/orders |
| Company sales limit | `limit` | `exceeded`, `current`, `limit`, `reason` |

Pending order statuses considered: `APPROVAL`, `ORDER`, `DELIVERY`.

---

## 8. Permissions

```php
'sales' => [
    'sales request' => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
    'sales order'   => ['view', 'approve', 'reject'],
],
'extra' => [
    'sales request' => ['override price', 'override tax'],
],
```

| Permission | Purpose |
|---|---|
| `override price sales request` | Change `price_proposed` away from the resolved PriceResolutionHelper value |
| `override tax sales request` | Change `tax_mode` / `tax_id` away from the company's default tax |

---

## 9. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `sales.request.index.init` | `/cmw/sales/requests` | `Sales\Request\Index\Init` |
| `sales.request.create` | `/cmw/sales/requests/create` | `Sales\Request\Create` |
| `sales.request.edit` | `/cmw/sales/requests/{id}/edit` | `Sales\Request\Edit` |
| `sales.order.approval.index` | `/cmw/sales/orders/approval` | `Sales\Approval\Index` |
| `sales.order.approval.show` | `/cmw/sales/orders/approval/{id}` | `Sales\Approval\Show` |
| `sales.order.index.ongoing` | `/cmw/sales/orders` | `Sales\Order\Index\Ongoing` |
| `sales.order.index.rejected` | `/cmw/sales/orders/rejected` | `Sales\Order\Index\Rejected` |
| `sales.order.index.cancelled` | `/cmw/sales/orders/cancelled` | `Sales\Order\Index\Cancelled` |
| `sales.order.show` | `/cmw/sales/orders/{id}` | `Sales\Order\Show` |

---

## 10. Sidebar Menus

| Label | Icon | Permission | Route |
|-------|------|------------|-------|
| Sales Requests | `document-text` | `view sales request` | `sales.request.index.init` |
| SO Approval | `shield-check` | `view/approve sales order` | `sales.order.approval.index` |
| Ongoing Orders | `truck` | `view sales order` | `sales.order.index.ongoing` |
| Rejected Orders | `x-circle` | `view sales order` | `sales.order.index.rejected` |
| Cancelled Orders | `no-symbol` | `view sales order` | `sales.order.index.cancelled` |

---

## 11. Events

| Event | Dispatched When |
|-------|----------------|
| `sales.request.refresh.init` | SR created, restored to draft, replicated |
| `shp.sales.order.refresh.approval` | SR submitted, approval action taken |
| `shp.sales.order.refresh.ongoing` | Order approved |
| `shp.sales.order.refresh.rejected` | Order rejected |
| `shp.sales.order.refresh.cancelled` | Order cancelled |

---

## 12. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Transaction/OrderHeader.php`, `OrderDetail.php`, `ReturnDetail.php` |
| SR Components | `app/Livewire/Sales/Request/` |
| SO Approval | `app/Livewire/Sales/Approval/` |
| SO Views | `app/Livewire/Sales/Order/` |
| Helpers | `CodeGeneratorHelper`, `TransactionHelper`, `CustomerCheckHelper`, `PriceResolutionHelper` |
| Migrations | `2025_12_23_102400_*`, `2025_12_23_103800_*` |
| Permissions | `app/Helpers/CMW/PermissionHelper.php` |
