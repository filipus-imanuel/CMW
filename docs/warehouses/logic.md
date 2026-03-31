# Warehouses Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The Warehouses module handles all warehouse-side operations: **outbound delivery** of sales orders and **inbound receipt** of returned goods. It acts as the physical execution layer between Sales transactions and Inventory ledger updates.

### Sub-Modules

| Sub-Module | Folder | Purpose |
|------------|--------|---------|
| Delivery | `Warehouses\Delivery\` | Outbound shipments from SO → DO → Invoice |
| Return | `Warehouses\Return\` | Inbound receipt of returned goods |

> Detailed logic for each sub-module is documented separately:
> - [Delivery Logic](delivery/logic.md)
> - [Return Logic](return/logic.md)

---

## 2. Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Delivery Header | `delivery_headers` | `DeliveryHeader` | Outbound delivery document |
| Delivery Detail | `delivery_details` | `DeliveryDetail` | Line items per delivery |
| Return Header | `return_headers` | `ReturnHeader` | Return document from customer |
| Return Detail | `return_details` | `ReturnDetail` | Line items per return |
| Inventory Ledger | `inventory_ledgers` | `InventoryLedger` | Stock movement journal |
| Inventory Damaged Stock | `inventory_damaged_stocks` | `InventoryDamagedStock` | Damaged goods from returns |

---

## 3. Permission Matrix

| Permission | Group | Actions |
|------------|-------|---------|
| `delivery order` | `sales` | `view`, `create`, `confirm`, `cancel`, `force finish` |
| `warehouse return` | `warehouse` | `view`, `receive` |

---

## 4. Inventory Ledger Integration

All warehouse operations write to `inventory_ledgers` table:

| Operation | Type | Effect |
|-----------|------|--------|
| DO Created | `sales` | `quantity_out` (stock deducted) |
| DO Cancelled | `sales` | `quantity_in` (reversal) |
| Return Receipt (good) | `sales_return` | `quantity_in` (stock returned) |
| Return Receipt (damaged) | — | `inventory_damaged_stocks` record |

All quantities are converted to base UOM using `ItemUom.conversion_rate` before writing to the ledger.

---

## 5. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `warehouses.delivery.*` | `/cmw/warehouses/delivery/*` | `Warehouses\Delivery\*` |
| `warehouses.return.index` | `/cmw/warehouses/return` | `Warehouses\Return\Index` |
| `warehouses.return.show` | `/cmw/warehouses/return/{id}` | `Warehouses\Return\Show` |

---

## 6. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Transaction/DeliveryHeader.php`, `DeliveryDetail.php` |
| Models (Return) | `app/Models/CMW/Transaction/ReturnHeader.php`, `ReturnDetail.php` |
| Models (Inventory) | `app/Models/CMW/Inventory/InventoryLedger.php`, `InventoryDamagedStock.php` |
| Delivery Components | `app/Livewire/Warehouses/Delivery/` |
| Return Components | `app/Livewire/Warehouses/Return/` |
| Helpers | `CodeGeneratorHelper`, `TransactionHelper` |
