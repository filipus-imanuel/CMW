# Warehouse Delivery Module — Logic Documentation

## Module Overview

The Warehouse Delivery module bridges **Sales Orders (SO)** and **AR Invoices**, enabling warehouse staff to manage outbound shipments. It supports partial deliveries, multi-warehouse sourcing per line item, and automatic invoice generation upon delivery confirmation.

---

## Status Flow

### SO Status Transitions (driven by delivery lifecycle)

```
ORDER ──(1st DO created)──► DELIVERY ──(all qty received)──► FINISH ──► FINAL
                                │                               ▲
                                └──(force finish)───────────────┘
```

- **ORDER → DELIVERY**: When the first Delivery Order is created from this SO.
- **DELIVERY → FINISH**: When all ordered quantities across all SO lines are confirmed as received via finished DOs. Also triggered by Force Finish.
- If all DOs are cancelled and no active ones remain, **DELIVERY → ORDER** (revert).

### Delivery Order (DO) Status

| Status       | Description                                            |
|-------------|--------------------------------------------------------|
| `ongoing`   | Created and sent, awaiting customer receipt confirmation |
| `finished`  | Customer confirmed receipt; AR invoice auto-generated   |
| `cancelled` | Cancelled with reason; stock reversed                   |

---

## Database Schema

### delivery_headers

| Column          | Type           | Description                                |
|----------------|----------------|--------------------------------------------|
| id             | bigint PK      | Auto-increment                             |
| code           | string(50)     | Unique, format: `DO/YYMM/NNNN`            |
| date           | date           | Delivery date                              |
| order_header_id| FK (order_headers) | Source Sales Order                     |
| partner_id     | FK (partners)  | Customer                                   |
| company_id     | FK (companies) | Selling company (nullable)                 |
| currency_id    | FK (currencies)| Transaction currency (default: 1)          |
| status         | string(20)     | ongoing / finished / cancelled             |
| cancel_reason  | string(1024)   | Reason for cancellation (nullable)         |
| confirmed_by   | FK (users)     | User who confirmed receipt (nullable)      |
| confirmed_at   | timestamp      | When receipt was confirmed (nullable)      |
| subtotal       | decimal(13,2)  | Sum of line subtotals                      |
| tax            | decimal(13,2)  | Sum of line taxes                          |
| total          | decimal(13,2)  | Grand total                                |
| delivery_address | string(1024) | Editable delivery address (nullable)       |
| + BaseModel columns | ...       | code, name, remarks, audit, soft delete    |

### delivery_details

| Column            | Type              | Description                          |
|------------------|-------------------|--------------------------------------|
| id               | bigint PK         | Auto-increment                       |
| delivery_header_id | FK (delivery_headers) | Parent delivery               |
| order_detail_id  | FK (order_details)| Corresponding SO line                |
| item_id          | FK (items)        | Item being shipped                   |
| item_uom_id      | FK (item_uoms)    | Item UOM (nullable)                  |
| warehouse_id     | FK (warehouses)   | Source warehouse (per-line)          |
| quantity_sent    | decimal(13,2)     | Quantity shipped                     |
| quantity_received| decimal(13,2)     | Quantity confirmed received          |
| price            | decimal(13,2)     | Unit price (from SO deal price)      |
| discount         | decimal(13,2)     | Proportional discount                |
| tax              | decimal(13,2)     | Calculated tax                       |
| total            | decimal(13,2)     | Line total                           |
| + BaseModel columns | ...            | audit, soft delete                   |

---

## Multi-Warehouse Design

Each delivery detail line has its own `warehouse_id`. This allows a single DO to source items from different warehouses. During creation, warehouse options are filtered based on:

1. `company_warehouses` — warehouses assigned to the SO's company
2. `item_warehouses` — warehouses that stock the specific item

The intersection of both determines the available warehouse options per line.

---

## Partial Delivery Logic

1. When creating a DO, the system calculates **remaining quantity** per SO line:
   `remaining = ordered_qty - sum(sent_qty from non-cancelled DOs)`
2. User specifies `quantity_sent` for each line (≤ remaining).
3. Lines with `remaining = 0` are disabled and hidden.
4. Multiple DOs can be created for the same SO until all quantities are fulfilled.

### Proportional Discount

When a partial delivery is made, the discount is proportionally allocated:
```
line_discount = original_SO_discount × (quantity_sent / quantity_ordered)
```

---

## Stock Management

### Delivery Address

During DO creation, the partner's default address is auto-loaded into a searchable dropdown. The user can select from available partner addresses, and the selected address is populated into an editable text field (`delivery_address`). The address can be freely modified before saving. Stored as `string(1024)` on `delivery_headers`.

### On DO Creation (Create.php)
- Stock is **deducted** immediately via `InventoryLedger` entries:
  - `type = 'sales'`
  - `quantity_out = quantity_sent` (converted to base UOM if applicable)
  - `reference_type = DeliveryHeader::class`
  - `reference_id = delivery_header_id`
- Stock availability is validated before creation.

### On DO Cancellation (Ongoing/Show.php → cancelDelivery)
- **Reversal** entries are created in `InventoryLedger`:
  - `quantity_in = quantity_sent` (returned to inventory)
  - `remarks = 'REVERSAL - Delivery Order: {code}'`

---

## Auto-Billing (AR Invoice)

When a delivery is confirmed (`confirmReceipt`) or force-finished (`forceFinish`):

1. An `ArInvoiceHeader` is automatically created:
   - Code: `INV/YYMM/NNNN` (via `CodeGeneratorHelper::generateInvoiceCode()`)
   - Links to both `delivery_header_id` and `order_header_id`
   - Amounts calculated from `quantity_received × price` with proportional discount and tax
   - Status: `unpaid`, balance = total

2. The invoice uses the delivery's quantity_received (which may differ from quantity_sent if customer received less).

---

## Permission Matrix

| Permission               | Actions Enabled                        | Default Roles |
|--------------------------|----------------------------------------|---------------|
| view delivery order      | View all delivery indexes and details  | Warehouse     |
| create delivery order    | Create new DOs from upcoming SO list   | Warehouse     |
| confirm delivery order   | Confirm receipt (finish DO, create invoice) | Warehouse |
| cancel delivery order    | Cancel ongoing DO (reverse stock)      | Warehouse     |
| force finish delivery order | Force finish DO (skip remaining qty) | Warehouse     |

All actions use the **double gate** pattern: `@can()` in Blade + `$this->authorize()` in PHP.

---

## Routes

| Route Name                             | URL                                    | Component                |
|---------------------------------------|----------------------------------------|--------------------------|
| warehouses.delivery.upcoming          | /cmw/warehouses/delivery/upcoming      | Upcoming\Index           |
| warehouses.delivery.create            | /cmw/warehouses/delivery/create/{orderId} | Create                |
| warehouses.delivery.ongoing.index     | /cmw/warehouses/delivery/ongoing       | Ongoing\Index            |
| warehouses.delivery.ongoing.so        | /cmw/warehouses/delivery/ongoing/so    | Ongoing\So               |
| warehouses.delivery.ongoing.show      | /cmw/warehouses/delivery/ongoing/{id}  | Ongoing\Show             |
| warehouses.delivery.finish.index      | /cmw/warehouses/delivery/finish        | Finish\Index             |
| warehouses.delivery.finish.show       | /cmw/warehouses/delivery/finish/{id}   | Finish\Show              |
| warehouses.delivery.cancelled.index   | /cmw/warehouses/delivery/cancelled     | Cancelled\Index          |
| warehouses.delivery.cancelled.show    | /cmw/warehouses/delivery/cancelled/{id}| Cancelled\Show           |

---

## Post-Action Redirects

After status-changing actions on `Ongoing/Show`, the user is redirected to the appropriate show page:

| Action          | Redirects To          |
|-----------------|-----------------------|
| Confirm Receipt | `finish.show`         |
| Force Finish    | `finish.show`         |
| Cancel          | `cancelled.show`      |
| Re-send Request | stays on `ongoing.show` (no status change) |

## Events

| Event Name                                  | Dispatched When                |
|--------------------------------------------|-------------------------------|
| shp.warehouse.delivery.created             | New DO created                |

---

## File Structure

```
app/
  Livewire/
    Warehouses/
      Delivery/
        Create.php                  # Create DO from SO
        Upcoming/
          Index.php                 # Upcoming SO list wrapper
          IndexDataTable.php        # Rappasoft DataTable
          Show.php                  # SO detail for warehouse delivery
        Ongoing/
          Index.php                 # Ongoing DO list wrapper
          IndexDataTable.php        # Rappasoft DataTable
          So.php                    # Ongoing SO list wrapper
          SoDataTable.php           # Rappasoft DataTable
          Show.php                  # DO detail with actions
        Finish/
          Index.php                 # Finished DO list wrapper
          IndexDataTable.php        # Rappasoft DataTable
          Show.php                  # Read-only finished DO detail
        Cancelled/
          Index.php                 # Cancelled DO list wrapper
          IndexDataTable.php        # Rappasoft DataTable
          Show.php                  # Read-only cancelled DO detail
  Models/CMW/Transaction/
    DeliveryHeader.php            # Delivery order header model
    DeliveryDetail.php            # Delivery order detail model
resources/views/livewire/warehouses/delivery/
    create.blade.php
    upcoming/index.blade.php
    upcoming/show.blade.php
    ongoing/index.blade.php
    ongoing/so.blade.php
    ongoing/show.blade.php
    finish/index.blade.php
    finish/show.blade.php
    cancelled/index.blade.php
    cancelled/show.blade.php
```
