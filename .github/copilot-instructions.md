# SHP ERP System - Development Guide

**Purpose**: Laravel 12 + Livewire 3 ERP system for SHP manufacturing  
**Core Workflow**: Raw Materials → Production → Finished Goods → Sales  
**Last Updated**: 2025-11-23

---

## 🚀 Quick Start

```powershell
# First-time setup
composer install
php artisan migrate
php artisan permission:sync

# Daily development (runs serve + queue + vite concurrently)
composer run dev
```

---

## 📁 Tech Stack

- **Backend**: Laravel 12, SQLite (dev), Spatie Permissions
- **Frontend**: Livewire 3, Flux Pro (UI), Volt (SFC), Vite
- **Storage**: DigitalOcean Spaces (S3-compatible)

---

## 🔑 Critical Coding Rules

### 1. UI Components - Flux Pro Only

**ALWAYS use Flux components** - No custom HTML/CSS unless absolutely necessary.

**📚 Complete Documentation**: See `docs/flux/` for full reference
- `docs/flux/components/` - All 39 components (button, badge, table, form fields, etc.)
- `docs/flux/guides/` - Patterns, principles, theming, dark mode
- `docs/flux/layouts/` - Header, sidebar layouts

**Quick Reference**:
- **Buttons**: Use only valid variants: `primary`, `danger`, `ghost`, `outline`, `filled`, `subtle`
- **Badges**: Available colors: zinc, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose
- **Date Pickers**: Use `<flux:date-picker>` with Carbon properties, NEVER `<flux:input type="date">`
- **Tables**: NEVER add background colors to `<flux:table.row>` or `<flux:table.cell>`
- **Callouts**: Use for status messages (amber=warning, red=error, green=success, blue=info)
- **Form Fields**: Use `label="..."` and `badge="Required"` attributes directly on inputs
- **Modal Control**: `Flux::modal('name')->show()` to open, `$this->modal('name')->close()` to close

### 2. DataTable Components (Rappasoft LaravelLivewireTables)

**CRITICAL RESTRICTION**:
- **NO Flux components** in `Column::format()` except icons
- **Flux icons ONLY** work: `<flux:icon.eye>`, `<flux:icon.pencil>`, `<flux:icon.trash>`
- **Use pure Tailwind HTML** for badges/buttons in datatable columns
- **Always check permissions** before rendering action buttons

### 3. Transactions & Data Integrity

```php
// ALWAYS wrap DB mutations in transactions
DB::transaction(function() {
    // Lock rows for financial aggregates
    $order->lockForUpdate()->update([...]);
    
    // Never trust client totals - recalculate server-side
    TransactionHelper::updatePurchaseOrderHeaderTotals($order);
    
    // Track changes
    'updated_by' => Auth::id(),
});
```

### 4. Totals Calculation

- **Use TransactionHelper** - never recalculate manually in components
- **Formula**: `grand_total = total_amount - total_discount + tax_amount + total_cost`
- **Tax IN** (inclusive): Price normalized via `price / (1 + tax%)`
- **Tax EX** (exclusive): Tax added on subtotal after discount
- **Always use** `sanitize_numeric()` on user input before arithmetic

### 5. File Uploads

```php
use Livewire\WithFileUploads;  // ⚠️ CRITICAL: Always add this trait!

class Create extends Component {
    use WithFileUploads;
    
    public $photo_file;
    
    public function save() {
        $path = FileUploadHelper::uploadFile($this->photo_file, 'Sales/Costs');
    }
}
```

### 6. User Feedback & Events

```php
// Toast notifications
Flux::toast('Saved successfully', variant: 'success', position: 'top-end');

// Dispatch events after state changes
$this->dispatch('shp.{module}.{entity}.{action}');

// Refresh components
$model = $model->fresh(['relations']);
```

### 7. Livewire Component Lifecycle

```php
public function mount($id) {
    $this->authorize('action resource');  // Permission gate
    $this->model = Model::findOrFail($id);
    
    // Status guard - redirect if not editable
    if ($this->model->status != 'DRAFT') {
        $this->redirectRoute('route.show', ['id' => $id], navigate: true);
    }
    
    $this->handlePopulateInputs();
}

public function save() {
    $this->validate();
    
    DB::transaction(function() {
        $this->model->update([
            'field' => $value,
            'updated_by' => Auth::id(),
        ]);
        
        TransactionHelper::updatePurchaseOrderHeaderTotals($this->model);
    });
    
    Flux::toast('Saved successfully', variant: 'success', position: 'top-end');
    $this->dispatch('shp.module.entity.refresh');
}
```

### 8. Permissions (Spatie)

```php
// 1. Add to PermissionHelper::master()
'extra' => [
    'resource' => ['custom action'],
],

// 2. Sync permissions
php artisan permission:sync

// 3. Double gate in UI & server
@can('action resource')
    <flux:button wire:click="action">Action</flux:button>
@endcan

public function action() {
    $this->authorize('action resource');
    // ...logic
}
```

**Naming**: Singular resource + action (e.g., `create sales request`, not `create sales requests`)

### 9. Code Generation

- **Orders**: `TYPE/YYMM/####` - `CodeGeneratorHelper::generateOrderNumber('SO')`
- **Deliveries**: `DLV/TYPE/YYMM/####` - `CodeGeneratorHelper::generateDeliveryNumber('SO')`
- **Billing**: `BILL/TYPE/YYMM/####` - `CodeGeneratorHelper::generateBillingNumber('SO')`
- **Items**: Use `ItemCodeGeneratorHelper` for SKU generation

### 10. Production Workflow

**Status Flow**: `INIT` → `WAREHOUSE` → `WASHING` → `PRODUCTION` → `FINISHED`

**Rollback Feature**: 
- From WAREHOUSE/WASHING → back to INIT to add missing RM items
- Preserves ALL existing data (measurements, intermediates)
- Permission: `rollback production`
- See `docs/production/rollback-to-edit.md` for details

**Data Structure**: FG-centric with nested intermediates array

---

## 📁 Directory Structure

```
app/
  Helpers/              # Autoloaded utilities
    TransactionHelper.php        # Totals calculation
    HandlingPopulateHelper.php   # Select options
    ItemCodeGeneratorHelper.php  # SKU generation
    RegisteredHelper.php         # Global functions
    SHP/PermissionHelper.php     # Permission matrix
  Livewire/Shp/         # Feature modules
    {Module}/{Entity}/{Action}.php  # Create|Edit|Index|IndexDataTable|Show|Search
  Models/SHP/
    Master/              # Static data (ItemCategory, Units, Partner)
    Transaction/         # Orders, OrderDetail, Cost, Delivery, Billing
    Production/          # Production, Intermediates, FinishedGood
    Inventory/           # Item, ItemWarehouse, ItemWarehouseLog
```

---

## ⚠️ Common Gotchas

- **Stale totals**: Forgot to dispatch refresh event after mutations
- **Double formatting**: Don't run `format_number()` on already formatted input
- **Status guard**: Always add status check in `mount()` to prevent editing wrong status
- **Stock check**: Use `getTotalAvailableSack()` for availability, not `getTotalAvailableQty()`
- **File uploads**: ALWAYS add `WithFileUploads` trait

---

## ✅ Pre-Commit Checklist

- [ ] DB migration + model `$fillable`/`$casts` updated
- [ ] Validation rules centralized in component `rules()`
- [ ] Permissions added to `PermissionHelper::master()` + synced
- [ ] Transaction wrapper + `lockForUpdate()` for financial mutations
- [ ] Events dispatched after state changes
- [ ] Module docs updated in `docs/{module}/`
- [ ] No debug calls (`dd()`, `dump()`, `var_dump()`)

---

## 🔐 Security Checklist

- [ ] Double gate: `@can()` + `$this->authorize()`
- [ ] Never trust client totals - recalculate server-side
- [ ] Validate file uploads (MIME, size)
- [ ] Sanitize numeric input with `sanitize_numeric()`
- [ ] Use `lockForUpdate()` on financial aggregates
- [ ] Audit trail: populate `created_by`, `updated_by`, `deleted_by`

---

## 📚 Complete Documentation

### Flux Pro UI Components
- **Components**: `docs/flux/components/` (39 components with examples)
- **Guides**: `docs/flux/guides/` (patterns, principles, theming)
- **Layouts**: `docs/flux/layouts/` (header, sidebar)

### Domain Modules
- **Sales**: `docs/sales/` (Request, Order, Delivery, Billing, Payment)
- **Production**: `docs/production/` (Lifecycle, rollback, FG templates)
- **Warehouse**: `docs/warehouse/` (Fulfillment, delivery processes)
- **Architecture**: `docs/architecture/` (Cross-cutting patterns, UI patterns)

### Helper Responsibilities

| Helper | Purpose | Key Methods |
|--------|---------|-------------|
| `TransactionHelper` | Totals calculation, qty aggregation | `updatePurchaseOrderHeaderTotals($order)` |
| `HandlingPopulateHelper` | Select options (customers, items) | `handlePopulateCustomers($filters)` |
| `PermissionHelper` | Permission matrix + sync | `master()`, `sync($roles)` |
| `RegisteredHelper` | Global utilities | `sanitize_numeric()`, `format_number()` |
| `ItemCodeGeneratorHelper` | SKU generation | `generateFinishedGoodCode(...)` |
| `FileUploadHelper` | S3 uploads | `uploadFile($file, 'path')` |

---

## 🎯 Event System

**Naming**: `shp.{module}.{entity}.{action}`

**Pattern**: Mutation → `fresh(['relations'])` → dispatch event → listener `fresh()` + repopulate

Examples:
- `shp.sales.request.edit.refresh_order_detail` - Refresh header+details after cost/detail changes
- `shp.sales.billing.show.refresh` - Refresh billing page after payment changes
- `shp.production.refresh_intermediates` - Reload FG intermediates after washing update

---

## 📖 When You Need More Details

This document provides quick reference for daily development. For comprehensive information:

1. **UI Components** → See `docs/flux/components/{component-name}.md`
2. **Business Logic** → See `docs/{module}/` (sales, production, warehouse)
3. **Patterns & Architecture** → See `docs/architecture/`
4. **Testing** → Run `./vendor/bin/pest` or `composer test`

**Remember**: Reference the detailed docs instead of duplicating information. Keep this file focused on quick, actionable guidance.
