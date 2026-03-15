# Sales Module — Business Logic

**Last Updated**: 2026-03-13

---

## 1. Domain Overview

The Sales module manages the lifecycle of sales from **request → approval → order**. A single `OrderHeader` record progresses through statuses; no parent-child duplication.

### Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Order Header | `order_headers` | `OrderHeader` | Sales request / order header |
| Order Detail | `order_details` | `OrderDetail` | Line items with pricing |
| Delivery Schedule | `order_delivery_schedules` | `OrderDeliverySchedule` | Partial delivery dates per line item |
| AR Invoice Header | `ar_invoice_headers` | `ArInvoiceHeader` | Invoice generated per delivery order |
| AR Payment Header | `ar_payment_headers` | `ArPaymentHeader` | Customer payment record |
| AR Payment Detail | `ar_payment_details` | `ArPaymentDetail` | Payment-to-invoice allocation |
| Payment Method | `payment_methods` | `PaymentMethod` | Master: payment method types |

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
| `DELIVERY`+ | Delivery in progress | No | `scopeOrders` |
| `FINISH` | All deliveries completed | No | `scopeOrders` |
| `FINAL` | All invoices fully paid | No | `scopeOrders` |

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

### 4.5 Partial Delivery Schedule (`Sales\Request\DeliverySchedule`)

Allows salespersons to split each order detail's quantity across multiple delivery dates.

#### Table: `order_delivery_schedules`

| Column | Type | Purpose |
|--------|------|---------|
| `order_header_id` | FK → `order_headers` | Parent order |
| `order_detail_id` | FK → `order_details` | Parent line item |
| `delivery_date` | `date` | Scheduled delivery date for this partial |
| `quantity` | `decimal(13,2)` | Quantity to deliver on this date |
| `remarks` | `string(1024)` | Optional notes |

#### Flow

1. Permission: `edit sales request` (same as Edit)
2. Only INIT status orders with at least 1 item
3. Per item, user adds schedule rows specifying `delivery_date` + `quantity`
4. Each item must have at least 1 schedule row
5. **Validation**: total scheduled quantity per item **must equal** the order detail quantity
6. On save, the header `delivery_date` is auto-updated to the **earliest** scheduled date
7. On submit (from Edit), if schedules exist, validates all items have balanced schedules

#### UI

- Accessible from Edit page via "Delivery Schedule" button
- Visual indicator on Edit page shows whether schedule is configured
- Read-only display on Approval Show and Order Show pages (collapsible per item)
- Each row shows: delivery date, quantity, percentage of total, remarks

#### Pre-fill Behavior

- When opening a fresh schedule (no existing rows), pre-fills 1 row per item with the header `delivery_date` and the item's full quantity
- Subsequent visits load existing schedule rows

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
    'sales request'  => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
    'sales order'    => ['view', 'approve', 'reject'],
    'delivery order'  => ['view', 'create', 'confirm', 'cancel', 'force finish'],
    'sales return'    => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
    'ar invoice'      => ['view'],
    'ar payment'      => ['view', 'create', 'cancel'],
    'payment method'  => ['view', 'create', 'edit', 'delete'],
],
'extra' => [
    'sales request' => ['override price', 'override tax'],
],
```

| Permission | Purpose |
|---|---|
| `override price sales request` | Change `price_proposed` away from the resolved PriceResolutionHelper value |
| `override tax sales request` | Change `tax_mode` / `tax_id` away from the company's default tax |
| `view ar invoice` | View unpaid/paid invoice lists and invoice detail |
| `view ar payment` | View active/cancelled payment lists and payment detail |
| `create ar payment` | Record a new payment from invoice detail |
| `cancel ar payment` | Cancel an active payment (reverses invoice balance) |
| `view payment method` | View payment methods master list |
| `create payment method` | Create a new payment method |
| `edit payment method` | Edit an existing payment method |
| `delete payment method` | Soft-delete a payment method |

### Role Assignments

| Role | AR Invoice | AR Payment | Payment Method |
|------|------------|------------|----------------|
| Super Admin | All | All | All |
| Management | view | view | view |
| Finance | view | view, create, cancel | view, create, edit, delete |
| Sales | view | — | — |
| Admin | — | — | — |

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
| `sales.order.show` | `/cmw/sales/orders/{id}` | `Sales\Order\Show` || `sales.invoice.index.unpaid` | `/cmw/sales/invoices` | `Sales\Invoice\Index\Unpaid` |
| `sales.invoice.index.paid` | `/cmw/sales/invoices/paid` | `Sales\Invoice\Index\Paid` |
| `sales.invoice.show` | `/cmw/sales/invoices/{id}` | `Sales\Invoice\Show` |
| `sales.payment.index.active` | `/cmw/sales/payments` | `Sales\Payment\Index\Active` |
| `sales.payment.index.cancelled` | `/cmw/sales/payments/cancelled` | `Sales\Payment\Index\Cancelled` |
| `sales.payment.create` | `/cmw/sales/payments/create/{invoiceId}` | `Sales\Payment\Create` |
| `sales.payment.show` | `/cmw/sales/payments/{id}` | `Sales\Payment\Show` |
| `masters.payment-methods.index` | `/cmw/masters/payment-methods` | `Masters\PaymentMethod\Index` |
---

## 10. Sidebar Menus

| Label | Icon | Permission | Route |
|-------|------|------------|-------|
| Sales Requests | `document-text` | `view sales request` | `sales.request.index.init` |
| SO Approval | `shield-check` | `view/approve sales order` | `sales.order.approval.index` |
| Ongoing Orders | `truck` | `view sales order` | `sales.order.index.ongoing` |
| Rejected Orders | `x-circle` | `view sales order` | `sales.order.index.rejected` |
| Cancelled Orders | `no-symbol` | `view sales order` | `sales.order.index.cancelled` |
| **Invoice** (group) | | `view ar invoice` | |
|   Unpaid | `document-currency-dollar` | `view ar invoice` | `sales.invoice.index.unpaid` |
|   Paid | `check-circle` | `view ar invoice` | `sales.invoice.index.paid` |
| **Payment** (group) | | `view ar payment` | |
|   Active | `banknotes` | `view ar payment` | `sales.payment.index.active` |
|   Cancelled | `no-symbol` | `view ar payment` | `sales.payment.index.cancelled` |
| Payment Methods | `banknotes` | (Master group) | `masters.payment-methods.index` |

---

## 11. Events

| Event | Dispatched When |
|-------|----------------|
| `sales.request.refresh.init` | SR created, restored to draft, replicated |
| `shp.sales.order.refresh.approval` | SR submitted, approval action taken |
| `shp.sales.order.refresh.ongoing` | Order approved |
| `shp.sales.order.refresh.rejected` | Order rejected |
| `shp.sales.order.refresh.cancelled` | Order cancelled || `shp.sales.invoice.refresh.unpaid` | Payment created or cancelled |
| `shp.sales.invoice.refresh.paid` | Payment created or cancelled |
| `shp.sales.payment.created` | New payment recorded |
| `shp.sales.payment.refresh.active` | Payment cancelled |
| `shp.sales.payment.refresh.cancelled` | Payment cancelled |
| `cmw.master.payment-method.refresh` | Payment method CRUD action |
---

## 12. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Transaction/OrderHeader.php`, `OrderDetail.php`, `ReturnDetail.php` |
| Models (AR) | `ArInvoiceHeader.php`, `ArPaymentHeader.php`, `ArPaymentDetail.php` |
| Models (Master) | `app/Models/CMW/Master/PaymentMethod.php` |
| SR Components | `app/Livewire/Sales/Request/` |
| SO Approval | `app/Livewire/Sales/Approval/` |
| SO Views | `app/Livewire/Sales/Order/` |
| Invoice Views | `app/Livewire/Sales/Invoice/` |
| Payment Views | `app/Livewire/Sales/Payment/` |
| Payment Method | `app/Livewire/Masters/PaymentMethod/` |
| Helpers | `CodeGeneratorHelper`, `TransactionHelper`, `CustomerCheckHelper`, `PriceResolutionHelper` |
| Migrations | `2025_12_23_102400_*`, `2025_12_23_102900_*`, `2025_12_23_103100_*`, `2025_12_23_103800_*`, `2025_12_23_104300_*`, `2026_03_13_000100_*` |
| Permissions | `app/Helpers/CMW/PermissionHelper.php` |

---

## 13. AR Invoice

### 13.1 Domain

One AR Invoice is generated per Delivery Order. The invoice tracks the total amount, how much has been paid, and the outstanding balance.

### 13.2 Invoice Status Flow

```
unpaid ──partial payment──▶ partial ──full payment──▶ paid
                                                       │
paid ◀──payment cancelled──── partial ◀──payment cancelled──┘
```

| Status | Meaning | Scope |
|--------|---------|-------|
| `unpaid` | No payments received | `scopeUnpaid` |
| `partial` | Some payments received, balance > 0 | `scopeUnpaid` |
| `paid` | Fully paid (balance = 0) | `scopePaid` |

### 13.3 Key Columns

| Column | Type | Purpose |
|--------|------|----------|
| `code` | `string(50)` | Unique invoice code (`INV/YYMM/0001`) |
| `date` | `date` | Invoice date |
| `due_date` | `date` | Payment due date |
| `order_header_id` | FK → `order_headers` | Linked SO |
| `delivery_header_id` | FK → `delivery_headers` | Linked DO (1:1 invoice per DO) |
| `subtotal` | `decimal(13,2)` | Pre-tax subtotal |
| `tax` | `decimal(13,2)` | Tax amount |
| `total` | `decimal(13,2)` | Grand total |
| `paid` | `decimal(13,2)` | Total payments received |
| `balance` | `decimal(13,2)` | Outstanding = total − paid |
| `status` | `string(20)` | `unpaid`, `partial`, `paid` |

### 13.4 Invoice Show Page (`Sales\Invoice\Show`)

- Permission: `view ar invoice`
- Displays: invoice info card, financial summary (subtotal/tax/total/paid/balance), delivery items table, payment history table
- **"Record Payment" button**: visible when `hasOutstandingBalance()` is true and user has `create ar payment` — links to Payment Create page
- Payment history shows each payment with code (linked), date, method, amount, status

### 13.5 Invoice Index Pages

| Page | Scope | Columns |
|------|-------|---------|
| Unpaid | `unpaid` (unpaid + partial) | Actions, Code, Date, Due Date, Customer, DO Code, SO Code, Total, Paid, Balance, Status |
| Paid | `paid` | Actions, Code, Date, Customer, DO Code, SO Code, Total, Paid, Status |

---

## 14. AR Payment

### 14.1 Domain

One payment record per invoice. Payment records the amount applied, and updates the invoice's `paid`, `balance`, and `status` fields accordingly. Payments can be cancelled with a reason, which reverses the invoice balance.

### 14.2 Payment Status

| Status | Meaning |
|--------|----------|
| `active` | Valid payment |
| `cancelled` | Reversed with reason |

### 14.3 Payment Code Format

```
FK/{company.payment_code}/YYMM/00001
```

- `FK` = fixed prefix
- `{company.payment_code}` = 2-digit code from `companies.payment_code` column
- `YYMM` = year-month
- `00001` = 5-digit sequential number

Generated by `CodeGeneratorHelper::generatePaymentCode($companyId)`.

### 14.4 Key Columns (`ar_payment_headers`)

| Column | Type | Purpose |
|--------|------|----------|
| `code` | `string(50)` | Unique payment code |
| `date` | `date` | Payment date |
| `currency_id` | FK → `currencies` | Copied from invoice |
| `partner_id` | FK → `partners` | Copied from invoice |
| `company_id` | FK → `companies` | From invoice's SO |
| `amount` | `decimal(13,2)` | Total payment amount |
| `payment_method_id` | FK → `payment_methods` | Selected payment method |
| `reference` | `string(255)` | Transfer ref, cheque no, etc. |
| `status` | `string(20)` | `active`, `cancelled` |
| `cancel_reason` | `string(1024)` | Reason (when cancelled) |

### 14.5 Payment Detail (`ar_payment_details`)

| Column | Type | Purpose |
|--------|------|----------|
| `ar_payment_header_id` | FK → `ar_payment_headers` | Parent payment |
| `ar_invoice_header_id` | FK → `ar_invoice_headers` | Target invoice |
| `amount` | `decimal(13,2)` | Amount applied to this invoice |

### 14.6 Recording a Payment (`Sales\Payment\Create`)

1. Permission: `create ar payment`
2. Accessed from Invoice Show page via "Record Payment" button
3. Mount loads the invoice; rejects if already `paid`
4. Form fields: date, amount (max = balance), payment_method_id, reference, remarks
5. On store (inside `DB::transaction` with `lockForUpdate`):
   - Validates amount ≤ invoice balance
   - Determines `company_id` from `invoice.orderHeader.company_id`
   - Generates payment code via `CodeGeneratorHelper::generatePaymentCode($companyId)`
   - Creates `ArPaymentHeader` + `ArPaymentDetail`
   - Updates invoice: `paid += amount`, `balance = total − paid`, status → `partial` or `paid`
   - Calls `TransactionHelper::checkAndUpdateOrderFinalStatus()` — if all SO invoices are paid → SO status = `FINAL`
   - Redirects to Payment Show

### 14.7 Cancelling a Payment (`Sales\Payment\Show`)

1. Permission: `cancel ar payment`
2. Cancel button visible only on `active` payments
3. Opens modal requiring cancellation reason
4. On cancel (inside `DB::transaction` with `lockForUpdate`):
   - Sets payment status → `cancelled`, stores `cancel_reason`
   - For each payment detail: reverses invoice balance (`paid -= amount`, recalculates `balance` and `status`)
   - Calls `TransactionHelper::checkAndUpdateOrderFinalStatus()` — if SO was `FINAL`, reverts to `FINISH`
   - Redirects to Cancelled Payments index

### 14.8 Payment Show Page

- Permission: `view ar payment`
- Displays: payment info card (code, date, status, customer, company, method, amount, reference, created by)
- Cancellation callout (red) shown if cancelled
- Applied Invoices table: invoice code (linked), SO code, DO code, invoice total, payment amount
- Cancel button with reason modal

### 14.9 Payment Index Pages

| Page | Scope | Columns |
|------|-------|---------|
| Active | `activePayments` | Actions, Code, Date, Customer, Company, Method, Amount, Reference, Status |
| Cancelled | `cancelled` | Actions, Code, Date, Customer, Company, Method, Amount, Cancel Reason, Status |

---

## 15. SO → FINAL Auto-Transition

`TransactionHelper::checkAndUpdateOrderFinalStatus($orderHeaderId)` handles automatic status transitions:

| Condition | Current Status | New Status |
|-----------|---------------|------------|
| All SO invoices are `paid` | `FINISH` | `FINAL` |
| Any SO invoice is not `paid` (e.g. payment cancelled) | `FINAL` | `FINISH` |
| SO status not `FINISH` or `FINAL` | — | No change |
| No invoices exist for SO | — | No change |

Called from:
- `Sales\Payment\Create::store()` — after recording payment
- `Sales\Payment\Show::cancelPayment()` — after reversing payment

---

## 16. Payment Method (Master)

Standard master CRUD (modal-based) under `Masters\PaymentMethod`.

| Field | Type | Rules |
|-------|------|-------|
| `code` | `string(50)` | Required, unique |
| `name` | `string(100)` | Required |
| `remarks` | `string(1024)` | Optional |
| `is_active` | `boolean` | Default true |

Components: `Index`, `IndexDataTable`, `Create`, `Edit`.

Event: `cmw.master.payment-method.refresh`.

### Company Payment Code

`companies.payment_code` (`string(2)`, nullable) — 2-digit code used in payment number generation format `FK/{payment_code}/YYMM/00001`. Configured in Company Create/Edit forms.
