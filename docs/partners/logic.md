# Partners Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The Partners module manages **customers** and **suppliers** using a shared `partners` table with boolean flags (`is_customer`, `is_supplier`). Each partner can have multiple **addresses** stored in `partner_addresses`.

### Sub-Modules

| Sub-Module | Folder | Purpose |
|------------|--------|---------|
| Customers | `Partners\Customers\` | Customer CRUD (modal-based) |
| CustomerAddresses | `Partners\CustomerAddresses\` | Customer address CRUD (modal-based) |
| Suppliers | `Partners\Suppliers\` | Supplier CRUD (modal-based) |
| SupplierAddresses | `Partners\SupplierAddresses\` | Supplier address CRUD (modal-based) |

---

## 2. Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Partner | `partners` | `App\Models\CMW\Master\Partner` | Shared customer/supplier record |
| Partner Address | `partner_addresses` | `App\Models\CMW\Master\PartnerAddress` | Addresses per partner |

---

## 3. Partner Model

### 3.1 Key Columns

| Column | Type | Purpose |
|--------|------|---------|
| `code` | string(50) | Unique partner code (from BaseModel) |
| `name` | string(100) | Partner name (from BaseModel) |
| `is_supplier` | boolean | Supplier flag |
| `is_customer` | boolean | Customer flag |
| `user_id` | FK → users | PIC/salesperson (nullable) |
| `credit_limit` | decimal(13,2) | Credit limit for customers |
| `category_price_id` | FK → category_prices | Pricing tier for this partner |
| `remarks` | string(500) | Optional notes |
| `is_active` | boolean | Active status (from BaseModel) |

### 3.2 Relationships

| Relation | Type | Target |
|----------|------|--------|
| `user()` | BelongsTo | `User` (PIC/salesperson) |
| `categoryPrice()` | BelongsTo | `CategoryPrice` |
| `addresses()` | HasMany | `PartnerAddress` |
| `companies()` | BelongsToMany | `Company` (pivot: `company_partner`) |

### 3.3 Scopes

| Scope | Filter |
|-------|--------|
| `suppliers()` | `is_supplier = true` |
| `customers()` | `is_customer = true` |
| `forSales($userId)` | `user_id = $userId AND is_customer = true` |

---

## 4. Customer CRUD

### 4.1 Create (`Partners\Customers\Create`)

- **Permission**: `create customer`
- **Pattern**: Modal-based (opened via event `cmw.partners.customers.create.open`)
- **Authorization**: in `openModal()` (not `mount()`)
- **Fields**: code (unique), name, category_price_id (optional), remarks, company_ids[]
- **On store**: Creates Partner with `is_customer = true`, syncs company pivot

### 4.2 Edit (`Partners\Customers\Edit`)

- **Permission**: `edit customer`
- **Pattern**: Modal-based (opened via event)
- **Fields**: Same as Create, email unique excludes current record
- **Additional**: Can update `user_id` (PIC assignment), `credit_limit`

### 4.3 Index

- **Permission**: `view customer`
- **Scope**: `Partner::customers()`
- **DataTable**: Rappasoft with search by code/name

---

## 5. Supplier CRUD

Mirror of Customer CRUD with `is_supplier = true` flag.

| Action | Permission | Pattern |
|--------|-----------|---------|
| Create | `create supplier` | Modal |
| Edit | `edit supplier` | Modal |
| View | `view supplier` | DataTable index |
| Delete | `delete supplier` | Soft delete |

---

## 6. Partner Addresses

### 6.1 Shared Table

Both customer and supplier addresses use the same `partner_addresses` table with FK to `partners`.

### 6.2 Key Columns

| Column | Type | Purpose |
|--------|------|---------|
| `partner_id` | FK → partners | Parent partner |
| `address` | text | Full address text |
| `city` | string | City |
| `province` | string | Province/state |
| `postal_code` | string | Postal code |
| `country_id` | FK → countries | Country reference |
| `phone` | string | Contact phone |
| `is_default` | boolean | Default address flag |

### 6.3 CRUD Pattern

- Modal-based, similar to Customer/Supplier
- Scoped to partner type (customer addresses vs supplier addresses)
- `view partner address`, `create partner address`, `edit partner address`, `delete partner address`

---

## 7. Business Logic

### 7.1 Company-Partner Association

Partners are linked to companies via `company_partner` pivot table. This controls:
- Which customers appear in Sales Request creation (filtered by selected company)
- Multi-company environments where the same customer may transact with different companies

### 7.2 PIC (Person In Charge) — Sales Only

`user_id` on Partner links a customer to a salesperson. Non-Super Admin sales users only see their assigned customers in Sales Request forms (via `scopeForSales`).

### 7.3 Category Price Tier

`category_price_id` determines the pricing tier used by `PriceResolutionHelper` to resolve item prices for this partner (see Inventory module docs).

### 7.4 Credit Limit

`credit_limit` is checked during SO Approval via `CustomerCheckHelper`. Used to warn/block approval if the customer's outstanding + pending orders exceed the limit.

---

## 8. Permission Matrix

| Permission | Actions |
|------------|---------|
| `customer` | `view`, `create`, `edit`, `delete` |
| `partner address` | `view`, `create`, `edit`, `delete` |
| `supplier` | `view`, `create`, `edit`, `delete` |

---

## 9. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `partners.customers.index` | `/cmw/partners/customers` | `Partners\Customers\Index` |
| `partners.customer-addresses.index` | `/cmw/partners/customer-addresses` | `Partners\CustomerAddresses\Index` |
| `partners.suppliers.index` | `/cmw/partners/suppliers` | `Partners\Suppliers\Index` |
| `partners.supplier-addresses.index` | `/cmw/partners/supplier-addresses` | `Partners\SupplierAddresses\Index` |

---

## 10. Related Files

| Area | Path |
|------|------|
| Partner Model | `app/Models/CMW/Master/Partner.php` |
| Partner Address Model | `app/Models/CMW/Master/PartnerAddress.php` |
| Customer Components | `app/Livewire/Partners/Customers/` |
| Customer Address Components | `app/Livewire/Partners/CustomerAddresses/` |
| Supplier Components | `app/Livewire/Partners/Suppliers/` |
| Supplier Address Components | `app/Livewire/Partners/SupplierAddresses/` |
| Populate Helper | `app/Helpers/CMW/PopulateDataHelper.php` |
| Customer Check Helper | `app/Helpers/CMW/CustomerCheckHelper.php` |
