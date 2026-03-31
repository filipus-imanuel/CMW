# Masters Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The Masters module manages all **master data** (reference/lookup data) used across the application. All master entities follow a standard modal-based CRUD pattern with Rappasoft DataTable listing.

### Sub-Modules

| Sub-Module | Folder | Table | Model | Purpose |
|------------|--------|-------|-------|---------|
| Company | `Masters\Company\` | `companies` | `Company` | Business entities (multi-company support) |
| Country | `Masters\Country\` | `countries` | `Country` | Country reference |
| Credit Term | `Masters\CreditTerm\` | `credit_terms` | `CreditTerm` | Payment terms (e.g., Net 30) |
| Currency | `Masters\Currency\` | `currencies` | `Currency` | Transaction currencies |
| Department | `Masters\Department\` | `departments` | `Department` | Organizational units |
| Employee | `Masters\Employee\` | `employees` | `Employee` | Employee records (separate from system users) |
| Exchange Rate | `Masters\ExchangeRate\` | `exchange_rates` | `ExchangeRate` | Currency conversion rates |
| Payment Method | `Masters\PaymentMethod\` | `payment_methods` | `PaymentMethod` | Payment method types for AR payments |
| Tax | `Masters\Tax\` | `taxes` | `Tax` | Tax types and rates |
| Uom | `Masters\Uom\` | `uoms` | `Uom` | Units of measure |
| Uom Conversion | `Masters\UomConversion\` | `uom_conversions` | `UomConversion` | Global UOM conversion rules |
| User Group | `Masters\UserGroup\` | `user_groups` | `UserGroup` | User grouping/classification |
| Warehouse | `Masters\Warehouse\` | `warehouses` | `Warehouse` | Physical warehouse locations |

---

## 2. Standard CRUD Pattern

All master entities follow a consistent implementation pattern:

### 2.1 Components per Entity

| Component | Purpose |
|-----------|---------|
| `Index.php` | Page wrapper, hosts DataTable + Create/Edit modals |
| `IndexDataTable.php` | Rappasoft DataTable: columns, search, sort, pagination |
| `Create.php` | Modal form for creating new records |
| `Edit.php` | Modal form for editing existing records |

### 2.2 Modal Pattern

- **Authorization**: Always in `openModal()`, NOT in `mount()`
- **Open via event**: e.g., `cmw.master.company.create.open`
- **Form state**: `$inputs[]` array pattern
- **Validation**: Centralized in `rules()` method
- **Store/Update**: Inside `DB::transaction()`
- **Audit fields**: `created_by` / `updated_by` set from `Auth::id()`
- **Event dispatch**: After mutation, dispatches refresh event for DataTable

### 2.3 DataTable Pattern

- Actions column first (using `components.datatables.datatable-action`)
- Columns: sortable + searchable where appropriate
- Soft deletes: records are soft-deleted, not hard-deleted
- Listener: refresh event from Create/Edit

---

## 3. Entity-Specific Details

### 3.1 Company

| Column | Type | Purpose |
|--------|------|---------|
| `code` | string(50) | Unique company code |
| `name` | string(100) | Company name |
| `sales_limit` | decimal(13,2) | Max outstanding sales per customer |
| `payment_code` | string(2) | 2-digit code for payment number generation (FK/{payment_code}/YYMM/00001) |
| `bank_name` | string | Company bank name |
| `bank_account_name` | string | Account holder name |
| `bank_account_number` | string | Account number |
| `currency_id` | FK → currencies | Default currency |
| `tax_mode` | string | `NONE`, `INCLUSIVE`, `EXCLUSIVE` |
| `tax_id` | FK → taxes | Default tax (when tax_mode ≠ NONE) |

**Pivots**:
- `company_item_category` — links companies to item categories
- `company_warehouses` — links companies to warehouses
- `company_partner` — links companies to partners (customers/suppliers)

### 3.2 Warehouse

| Column | Type | Purpose |
|--------|------|---------|
| `code` | string(50) | Unique warehouse code |
| `name` | string(100) | Warehouse name |
| `address` | text | Physical address |
| `is_active` | boolean | Active status |

**Relationships**:
- `companies()` — BelongsToMany via `company_warehouses`
- `items()` — BelongsToMany via `item_warehouses` (item whitelist)
- `inventoryLedgers()` — HasMany InventoryLedger

### 3.3 Tax

| Column | Type | Purpose |
|--------|------|---------|
| `code` | string(50) | Unique tax code (e.g., PPN) |
| `name` | string(100) | Tax name |
| `rate` | decimal(5,2) | Tax rate percentage |
| `is_active` | boolean | Active status |

### 3.4 Payment Method

| Column | Type | Purpose |
|--------|------|---------|
| `code` | string(50) | Unique code |
| `name` | string(100) | Display name (e.g., Transfer Bank, Cash, Giro) |
| `remarks` | string(1024) | Optional notes |
| `is_active` | boolean | Active status |

Used in AR Payment recording (`Sales\Payment\Create`). Event: `cmw.master.payment-method.refresh`.

### 3.5 Credit Term

| Column | Type | Purpose |
|--------|------|---------|
| `code` | string(50) | Unique code |
| `name` | string(100) | Term name (e.g., Net 30) |
| `days` | integer | Number of days until due |

Used to calculate AR invoice `due_date` from invoice date.

### 3.6 Exchange Rate

| Column | Type | Purpose |
|--------|------|---------|
| `from_currency_id` | FK → currencies | Source currency |
| `to_currency_id` | FK → currencies | Target currency |
| `rate` | decimal(13,6) | Conversion rate |
| `effective_date` | date | When this rate takes effect |

### 3.7 UOM Conversion

Global UOM conversion rules (separate from item-specific `item_uoms`):

| Column | Type | Purpose |
|--------|------|---------|
| `from_uom_id` | FK → uoms | Source UOM |
| `to_uom_id` | FK → uoms | Target UOM |
| `conversion_rate` | decimal(13,4) | How many target units per 1 source unit |

---

## 4. Permission Matrix

| Permission | Actions |
|------------|---------|
| `company` | `view`, `create`, `edit`, `delete` |
| `country` | `view`, `create`, `edit`, `delete` |
| `credit term` | `view`, `create`, `edit`, `delete` |
| `currency` | `view`, `create`, `edit`, `delete` |
| `department` | `view`, `create`, `edit`, `delete` |
| `employee` | `view`, `create`, `edit`, `delete` |
| `exchange rate` | `view`, `create`, `edit`, `delete` |
| `tax` | `view`, `create`, `edit`, `delete` |
| `uom` | `view`, `create`, `edit`, `delete` |
| `uom conversion` | `view`, `create`, `edit`, `delete` |
| `user group` | `view`, `create`, `edit`, `delete` |
| `warehouse` | `view`, `create`, `edit`, `delete` |

> **Note**: `payment method` permissions are in the `sales` permission group (not `master`), even though the Livewire component and route are under `Masters/`. See [Sales Logic](../sales/logic.md) §8.

---

## 5. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `masters.companies.index` | `/cmw/masters/companies` | `Masters\Company\Index` |
| `masters.countries.index` | `/cmw/masters/countries` | `Masters\Country\Index` |
| `masters.credit-terms.index` | `/cmw/masters/credit-terms` | `Masters\CreditTerm\Index` |
| `masters.currencies.index` | `/cmw/masters/currencies` | `Masters\Currency\Index` |
| `masters.departments.index` | `/cmw/masters/departments` | `Masters\Department\Index` |
| `masters.employees.index` | `/cmw/masters/employees` | `Masters\Employee\Index` |
| `masters.exchange-rates.index` | `/cmw/masters/exchange-rates` | `Masters\ExchangeRate\Index` |
| `masters.payment-methods.index` | `/cmw/masters/payment-methods` | `Masters\PaymentMethod\Index` |
| `masters.taxes.index` | `/cmw/masters/taxes` | `Masters\Tax\Index` |
| `masters.uom-conversions.index` | `/cmw/masters/uom-conversions` | `Masters\UomConversion\Index` |
| `masters.uoms.index` | `/cmw/masters/uoms` | `Masters\Uom\Index` |
| `masters.user-groups.index` | `/cmw/masters/user-groups` | `Masters\UserGroup\Index` |
| `masters.warehouses.index` | `/cmw/masters/warehouses` | `Masters\Warehouse\Index` |

---

## 6. Events

| Event | Entity | Trigger |
|-------|--------|---------|
| `cmw.master.company.refresh` | Company | Create/Edit/Delete |
| `cmw.master.country.refresh` | Country | Create/Edit/Delete |
| `cmw.master.credit-term.refresh` | Credit Term | Create/Edit/Delete |
| `cmw.master.currency.refresh` | Currency | Create/Edit/Delete |
| `cmw.master.department.refresh` | Department | Create/Edit/Delete |
| `cmw.master.employee.refresh` | Employee | Create/Edit/Delete |
| `cmw.master.exchange-rate.refresh` | Exchange Rate | Create/Edit/Delete |
| `cmw.master.payment-method.refresh` | Payment Method | Create/Edit/Delete |
| `cmw.master.tax.refresh` | Tax | Create/Edit/Delete |
| `cmw.master.uom.refresh` | UOM | Create/Edit/Delete |
| `cmw.master.uom-conversion.refresh` | UOM Conversion | Create/Edit/Delete |
| `cmw.master.user-group.refresh` | User Group | Create/Edit/Delete |
| `cmw.master.warehouse.refresh` | Warehouse | Create/Edit/Delete |

---

## 7. Related Files

| Area | Path |
|------|------|
| Models | `app/Models/CMW/Master/` |
| Components | `app/Livewire/Masters/` |
| Populate Helper | `app/Helpers/CMW/PopulateDataHelper.php` |
| Permission Helper | `app/Helpers/CMW/PermissionHelper.php` |
