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
- **ALWAYS wrap IndexDataTable** in `<flux:card>` component in index views

**Relationship Columns with Filters**:
When displaying relationship data that's filtered in whereHas:
- **NEVER add constraints to eager load** - use simple `->with(['relation'])`
- **Use base column field** (e.g., `partner_id`) not nested path (e.g., `partner.name`)
- **Format in format()** with null safety check
- **Custom searchable** for relationship fields

```php
// ❌ WRONG - constraint on eager load causes null
->with(['partner' => fn($q) => $q->where('is_customer', true)])

// ✅ CORRECT - filter only in whereHas
public function builder(): Builder {
    return Model::query()
        ->with(['partner'])  // Simple eager load
        ->whereHas('partner', fn($q) => $q->where('is_customer', true));
}

// Column definition
Column::make('Customer', 'partner_id')  // Use FK, not partner.name
    ->sortable()
    ->searchable(fn($query, $term) => 
        $query->orWhereHas('partner', fn($q) => 
            $q->where('name', 'like', "%{$term}%")))
    ->format(fn($value, $row) => 
        $row->partner ? "{$row->partner->name} ({$row->partner->code})" : 'N/A');
```

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
// Toast notifications (import Flux\Flux)
use Flux\Flux;

Flux::toast('Saved successfully', variant: 'success', position: 'top-end');

// Dispatch events after state changes
$this->dispatch('shp.{module}.{entity}.{action}');

// Refresh components
$model = $model->fresh(['relations']);
```

### 7. Livewire Component Input Properties

**CRITICAL PATTERN**: Use `$inputs[]` array for all form input values instead of individual public properties.

```php
use Livewire\Component;
use Livewire\Attributes\{Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use Flux\Flux;

#[Title('Component Title')]
class Create extends Component
{
    public $inputs = [];  // ✅ All form inputs in one array
    public $dropdown_data = [];  // For select options
    
    // ❌ AVOID: Individual properties
    // public $code;
    // public $name;
    // public $description;
    
    public function rules()
    {
        return [
            'inputs.code' => 'required|string|max:50',
            'inputs.name' => 'required|string|max:100',
            'inputs.description' => 'nullable|string|max:500',
        ];
    }
    
    #[On('module.entity.create.open')]
    public function openModal()
    {
        $this->authorize('create entity');
        $this->reset(['inputs']);
        $this->resetValidation();
        $this->loadDropdownData();
        $this->modal('create-entity')->show();
    }
    
    public function save()
    {
        $this->authorize('create entity');
        $validated = $this->validate();
        
        DB::transaction(function () use ($validated) {
            Entity::create([
                ...$validated['inputs'],
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        });
        
        Flux::toast('Created successfully', variant: 'success', position: 'top-end');
        $this->dispatch('module.entity.refresh');
        $this->modal('create-entity')->close();
    }
}
```

**Blade Binding**:
```blade
<flux:input wire:model="inputs.code" label="Code" />
<flux:input wire:model="inputs.name" label="Name" />
<flux:textarea wire:model="inputs.description" label="Description" />
```

### 8. Livewire Component Lifecycle

```php
use Livewire\Component;
use Livewire\Attributes\{Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use Flux\Flux;

#[Title('Component Title')]  // No Layout attribute - handled at app level
class ComponentName extends Component
{
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

### 8. Delete Confirmation Pattern

**ALWAYS implement standardized delete flow on Index components:**

```php
// Index.php Component
use Flux\Flux;
use Illuminate\Support\Facades\{Auth, DB};

public $deleteId = null;

#[On('module.entity.delete')]  // Or #[On('delete')] for generic pattern
public function confirmDelete($id): void
{
    $this->deleteId = $id;
    $this->modal('delete-entity-confirmation')->show();
}

public function destroy(): void
{
    if (!$this->deleteId) {
        return;
    }

    $this->authorize('delete entity');

    try {
        DB::transaction(function () {
            $entity = Model::findOrFail($this->deleteId);
            $entity->update(['deleted_by' => Auth::id()]);
            $entity->delete();

            Flux::toast('Entity deleted successfully', variant: 'success', position: 'top right');
            $this->dispatch('module.entity.refresh');
        });

        $this->deleteId = null;
        $this->modal('delete-entity-confirmation')->close();
    } catch (\Illuminate\Database\QueryException $e) {
        Flux::toast('Cannot delete entity. It may be in use.', variant: 'danger', position: 'top right');
    } catch (\Exception $e) {
        Flux::toast('An error occurred while deleting the entity.', variant: 'danger', position: 'top right');
    }
}
```

**index.blade.php view:**
```blade
{{-- Delete Confirmation Modal --}}
<flux:modal name="delete-entity-confirmation">
    <flux:heading>Delete Entity</flux:heading>
    <flux:subheading>Are you sure you want to delete this entity? This action cannot be undone.</flux:subheading>

    <div class="flex gap-2 mt-6">
        <flux:spacer/>
        <flux:button variant="danger" wire:click="destroy">Delete</flux:button>
        <flux:button variant="ghost" x-on:click="$flux.modal('delete-entity-confirmation').close()">Cancel</flux:button>
    </div>
</flux:modal>
```

**Key Points:**
- Use `x-on:click="$flux.modal('modal-name').close()"` for Cancel button (Alpine.js)
- Store `deleteId` to track what's being deleted
- Always wrap deletion in DB transaction
- Update `deleted_by` for audit trail before deleting
- Provide user-friendly error messages for constraint violations

### 9. Permissions (Spatie)

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

### 10. DataTable Action Column Pattern

**ALWAYS place Actions column first and use standardized component:**

```php
// IndexDataTable.php
public function columns(): array
{
    return [
        Column::make('Actions', 'id')
            ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                'rowId' => $row->id,
                'showEdit' => Auth::user()?->can('edit entity'),
                'editDispatchEvent' => 'module.entity.edit.open',
                'showDelete' => Auth::user()?->can('delete entity'),
                'deleteDispatchEvent' => 'module.entity.delete',  // Or 'delete' for generic
            ])),

        // ... other columns
    ];
}
```

**Key Points:**
- Actions column ALWAYS first
- Use `datatable-action` component for consistency
- Check permissions before showing buttons
- Use `BooleanColumn` for boolean fields (is_active, is_default)
- Never use custom HTML for action buttons

### 11. Code Generation

- **Orders**: `TYPE/YYMM/####` - `CodeGeneratorHelper::generateOrderNumber('SO')`
- **Deliveries**: `DLV/TYPE/YYMM/####` - `CodeGeneratorHelper::generateDeliveryNumber('SO')`
- **Billing**: `BILL/TYPE/YYMM/####` - `CodeGeneratorHelper::generateBillingNumber('SO')`
- **Items**: Use `ItemCodeGeneratorHelper` for SKU generation

### 12. Production Workflow

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
