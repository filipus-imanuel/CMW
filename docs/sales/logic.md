# Sales Module — Business Logic

**Last Updated**: 2026-02-23

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
                    │
                    ├──reject──▶ REJECTED
                    │
                    └──restore──▶ INIT
```

| Status | Meaning | Editable | Scope |
|--------|---------|----------|-------|
| `INIT` | Draft request | Yes | `scopeInit` |
| `APPROVAL` | Pending approval | No | `scopePendingApproval` |
| `ORDER` | Approved, ongoing | No | `scopeOngoing` |
| `REJECTED` | Rejected by approver | No | `scopeRejected` |
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
3. Sets status `INIT`, redirects to Edit

### 4.2 Edit (`Sales\Request\Edit`)

1. Permission: `edit sales request`
2. Only `INIT` status orders are editable
3. Fields: header info + `delivery_date` + line items
4. Items added via `SearchItem` modal (dispatches resolved price → `price_proposed`)
5. Price override requires `override price sales request` permission

### 4.3 Submit (INIT → APPROVAL)

Validation before submit:
- At least 1 detail item
- `delivery_date` required
- All items must have `price_deal > 0`

On submit: status changes to `APPROVAL`, dispatches `shp.sales.order.refresh.approval`.

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

- Read-only detail for `ORDER` or `REJECTED` status
- Shows both codes, approval info, rejection reason
- **Replicate** button (REJECTED only, requires `create sales request`):
  - Creates new `INIT` OrderHeader with fresh `code_request`
  - Copies details with `price_proposed = source.price_deal` (or `price_proposed` if no deal)
  - Redirects to Edit of new record

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
    'sales request' => ['override price'],
],
```

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
| `sales.order.show` | `/cmw/sales/orders/{id}` | `Sales\Order\Show` |

---

## 10. Sidebar Menus

| Label | Icon | Permission | Route |
|-------|------|------------|-------|
| Sales Requests | `document-text` | `view sales request` | `sales.request.index.init` |
| SO Approval | `shield-check` | `view/approve sales order` | `sales.order.approval.index` |
| Ongoing Orders | `truck` | `view sales order` | `sales.order.index.ongoing` |
| Rejected Orders | `x-circle` | `view sales order` | `sales.order.index.rejected` |

---

## 11. Events

| Event | Dispatched When |
|-------|----------------|
| `sales.request.refresh.init` | SR created, restored to draft, replicated |
| `shp.sales.order.refresh.approval` | SR submitted, approval action taken |
| `shp.sales.order.refresh.ongoing` | Order approved |
| `shp.sales.order.refresh.rejected` | Order rejected |

---

## 12. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Transaction/OrderHeader.php`, `OrderDetail.php` |
| SR Components | `app/Livewire/Sales/Request/` |
| SO Approval | `app/Livewire/Sales/Approval/` |
| SO Views | `app/Livewire/Sales/Order/` |
| Helpers | `CodeGeneratorHelper`, `TransactionHelper`, `CustomerCheckHelper`, `PriceResolutionHelper` |
| Migrations | `2025_12_23_102400_*`, `2025_12_23_103800_*` |
| Permissions | `app/Helpers/CMW/PermissionHelper.php` |
