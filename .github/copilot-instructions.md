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

## � Debugging & Verification

**CRITICAL RULE**: Never use `php artisan tinker` for verification scripts

**ALWAYS use `temp_debug/` folder**:
- Create standalone PHP scripts in `temp_debug/` directory for verification/debugging
- Run scripts with: `php temp_debug/script_name.php`
- Use descriptive names: `verify_permissions.php`, `check_roles.php`, etc.
- Include proper Laravel bootstrapping: `require __DIR__.'/../vendor/autoload.php'`
- Scripts are gitignored - safe for temporary debugging

**Script Template**:
```php
<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Your verification code here
```

---

## Tech Stack

- **Backend**: Laravel 12, SQLite (dev), Spatie Permissions
- **Frontend**: Livewire 3, Flux Pro (UI), Volt (SFC), Vite
- **Storage**: DigitalOcean Spaces (S3-compatible)

---

## 🔑 Critical Coding Rules

### 1. UI Components - Flux Pro Only

**ALWAYS use Flux components** - No custom HTML/CSS unless absolutely necessary.

**CRITICAL**: When creating or editing Flux components, ALWAYS read `docs/flux/components/{component-name}.md` first for complete documentation, examples, and proper usage patterns.

**Component Quick Reference**:

| Component | Doc File | Key Rules |
|-----------|----------|-----------|
| Button | [button.md](../docs/flux/components/button.md) | Variants: `primary`, `danger`, `ghost`, `outline`, `filled`, `subtle` |
| Badge | [badge.md](../docs/flux/components/badge.md) | Colors: zinc, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose |
| Switch | [switch.md](../docs/flux/components/switch.md) | Use for single boolean fields (`is_active`, `is_default`) |
| Checkbox | [checkbox.md](../docs/flux/components/checkbox.md) | Use for multiple choices. Wrap 2+ in `<flux:checkbox.group>` |
| Date Picker | [date-picker.md](../docs/flux/components/date-picker.md) | Use `<flux:date-picker>`, NEVER `<flux:input type="date">` |
| Table | [table.md](../docs/flux/components/table.md) | NEVER add background colors to rows/cells. **Notes column**: Use `class="max-w-md whitespace-pre-line break-words"` to preserve line breaks and wrap text. **Action column**: Use `class="align-middle"` on cell, wrap buttons in `<div class="flex items-center gap-3">` |
| Modal | [modal.md](../docs/flux/components/modal.md) | `Flux::modal('name')->show()` / `$this->modal('name')->close()` |
| Callout | [callout.md](../docs/flux/components/callout.md) | Colors: amber=warning, red=error, green=success, blue=info |
| Input/Textarea | [input.md](../docs/flux/components/input.md) | Use `label="..."` and `badge="Required"` attributes |

### 2. Database Schema Rules

**Field Type Standards**:
- **code**: Always `string(50)`, never enum
- **name**: `string(100)` for standard names, `string(255)` for longer names
- **remarks**: Always `string(1024)`, never `text`
- **description**: `string(1024)` for short descriptions, `text` only for long-form content (articles, HTML, etc.)

**Decimal Precision Standards**:
- **Monetary amounts, prices, quantities, rates**: Always `decimal(13, 2)` - supports up to 99,999,999,999.99 (11 digits before decimal, 2 after)
  - Examples: `rate`, `price`, `amount`, `cost_price`, `sell_price`, `subtotal`, `discount`, `tax`, `total`, `paid`, `balance`, `unit_cost`, `debit`, `credit`, `quantity`, `min_stock`, `max_stock`, `quantity_in`, `quantity_out`, `quantity_system`, `quantity_actual`, `conversion_rate`
- **Tax rates (percentages)**: Always `decimal(5, 2)` - supports 0.00% to 999.99%
  - Examples: `rate` in taxes table
- **Model casts**: Always use `'decimal:2'` for all numeric fields, `'boolean'` for all boolean fields
- **UI formatting**: Always use `number_format($value, 2)` for display
- **NEVER use** `decimal(18, 5)`, `decimal(18, 4)`, `decimal(12, 5)`, `decimal(15, 2)`, `decimal(18, 6)`, or `decimal(8, 4)` - these are legacy patterns

**Import Standards**:
- **Always use `use` statements** after namespace declaration, never inline fully qualified class names (FQCN)
- **Applies to**: Models, Helpers, Facades, Exceptions, and all PHP classes
- ❌ WRONG: `\App\Models\CMW\Master\Position::class`, `catch (\Illuminate\Database\QueryException $e)`, `catch (\Exception $e)`
- ✅ CORRECT: Add `use` statements at top, then use short class names

```php
// ❌ WRONG - Inline FQCN
class Create extends Component
{
    public function save()
    {
        try {
            $model = \App\Models\CMW\Master\Position::create([...]);
        } catch (\Illuminate\Database\QueryException $e) {
            // handle error
        } catch (\Exception $e) {
            // handle error
        }
    }
}

// ✅ CORRECT - Use statements
use App\Models\CMW\Master\Position;
use Exception;
use Illuminate\Database\QueryException;

class Create extends Component
{
    public function save()
    {
        try {
            $model = Position::create([...]);
        } catch (QueryException $e) {
            // handle error
        } catch (Exception $e) {
            // handle error
        }
    }
}
```

**Common Exception Imports**:
```php
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
```

**Model Fillable Standards**:
- **Always check BaseModel first** before defining `$fillable` in child models
- **Never duplicate fields** that are already in `BaseModel::getFillable()` (code, name, remarks, is_edit_locked, is_delete_locked, is_active, version_number, created_by, updated_by, deleted_by)
- **Only include model-specific fields** in child model's `$fillable` array

```php
// ❌ WRONG - Duplicating BaseModel fields
class Currency extends BaseModel
{
    protected $fillable = [
        'code',           // Already in BaseModel
        'name',           // Already in BaseModel
        'symbol',         // Currency-specific ✓
        'rate',           // Currency-specific ✓
        'remarks',        // Already in BaseModel
        'is_active',      // Already in BaseModel
        'created_by',     // Already in BaseModel
    ];
}

// ✅ CORRECT - Only model-specific fields
class Currency extends BaseModel
{
    protected $fillable = [
        'symbol',
        'symbol_position',
        'rate',
    ];
}
```

### 3. DataTable Components (Rappasoft LaravelLivewireTables)

**Action Column Pattern (CRITICAL)**:
- **Actions column ALWAYS first** in columns() array
- **Column name**: Use `'Actions'` (plural), never `'Action'`
- **Use standardized component**: `view('components.datatables.datatable-action', [...])`
- **NEVER use**: Inline HTML strings, `->html()`, or custom action view files
- **Permission checks**: Always use `Auth::user()?->can()` with null-safe operator

```php
use Illuminate\Support\Facades\Auth;

public function columns(): array
{
    return [
        Column::make('Actions', 'id')
            ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                'rowId' => $row->id,
                'enable_this_row' => !$row->trashed(),  // Disable if soft-deleted
                'showDetail' => Auth::user()?->can('view entity'),
                'detailHref' => route('entity.show', ['id' => $row->id]),
                'showEdit' => Auth::user()?->can('update entity'),
                'editHref' => route('entity.edit', ['id' => $row->id]),
                'showDelete' => Auth::user()?->can('delete entity'),
                'deleteDispatchEvent' => 'entity.delete',
            ])),
        // ... other columns
    ];
}
```

**Custom Action Buttons** (e.g., delivery creation):
- Add proper parameters to component, don't pass HTML strings
- Example: `'showDeliveryButton' => true`, `'deliveryButtonHref' => route(...)`

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

### 4. Transactions & Data Integrity

```php
use Illuminate\Support\Facades\{Auth, DB};
use App\Helpers\TransactionHelper;

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

### 5. Totals Calculation

- **Use TransactionHelper** - never recalculate manually in components
- **Formula**: `grand_total = total_amount - total_discount + tax_amount + total_cost`
- **Tax IN** (inclusive): Price normalized via `price / (1 + tax%)`
- **Tax EX** (exclusive): Tax added on subtotal after discount
- **Always use** `sanitize_numeric()` on user input before arithmetic

### 6. File Uploads

```php
use Livewire\WithFileUploads;  // ⚠️ CRITICAL: Always add this trait!
use App\Helpers\FileUploadHelper;

class Create extends Component {
    use WithFileUploads;
    
    public $photo_file;
    
    public function save() {
        $path = FileUploadHelper::uploadFile($this->photo_file, 'Sales/Costs');
    }
}
```

### 7. User Feedback & Events

```php
use Flux\Flux;

// Toast notifications
Flux::toast('Saved successfully', variant: 'success', position: 'top-end');

// Dispatch events after state changes
$this->dispatch('shp.{module}.{entity}.{action}');

// Refresh components
$model = $model->fresh(['relations']);
```

### 8. Livewire Component Input Properties

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
    
    public function store()
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

### 9. Livewire Component Lifecycle

**Modal Component Authorization (Create/Edit)**:
- **NEVER** use `$this->authorize()` in `mount()` for modal components included via `<livewire:...>` in Index pages
- **ALWAYS** place authorization in `openModal()` method (decorated with `#[On('xxx.create.open')]` or `#[On('xxx.edit.open')]`)
- Reason: `mount()` runs when Index page loads, causing 403 for users with only "view" permission
- Keep authorization in `store()`/`update()` methods as server-side double-check
- Wrap action buttons with `@can()` in blade views and check permissions in DataTable columns
- Reference: `Partners/Supplier`, `Masters/Employee`, `Masters/Position` for correct patterns

**Modal Component Initialization**:
- **NEVER** use `mount()` for dropdown/data initialization in modal components
- **ALWAYS** perform all initialization in `openModal()` method only
- Reason: Prevents redundant queries when Index page loads, only fetches data when modal opens
- Example: `handlePopulateDropdown()`, `loadDropdownData()` should only be called in `openModal()`

**CRUD Method Naming Convention (Laravel RESTful)**:
- **Create components**: Use `store()` method (not `save()`)
- **Edit components**: Use `update()` method (not `save()`)
- **Index components**: Use `destroy()` method for deletions
- **Blade forms**: Use `wire:submit="store"` for create, `wire:submit="update"` for edit
- Transaction components (Sales, Purchase, etc.) remain flexible based on business logic

```php
use Livewire\Component;
use Livewire\Attributes\{Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use Flux\Flux;
use App\Helpers\TransactionHelper;

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
}
```

### 10. Delete Confirmation Pattern

**ALWAYS implement standardized delete flow on Index components:**

```php
use Flux\Flux;
use Illuminate\Support\Facades\{Auth, DB};
use Exception;
use Illuminate\Database\QueryException;

// Index.php Component
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
    } catch (QueryException $e) {
        Flux::toast('Cannot delete entity. It may be in use.', variant: 'danger', position: 'top right');
    } catch (Exception $e) {
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
- Use `x-on:click="$flux.modal('modal-name').close()"` for Cancel button
- Store `deleteId` to track what's being deleted
- Always wrap deletion in DB transaction
- Update `deleted_by` for audit trail before deleting
- Provide user-friendly error messages for constraint violations

### 11. Permissions (Spatie)

```php
use App\Helpers\SHP\PermissionHelper;

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

### 12. DataTable Action Column Pattern

**ALWAYS place Actions column first and use standardized component:**

```php
use Illuminate\Support\Facades\Auth;

// IndexDataTable.php
public function columns(): array
{
    return [
        Column::make('Actions', 'id')
            ->format(fn ($value, $row, Column $column) => view('components.datatables.datatable-action', [
                'rowId' => $row->id,
                'enable_this_row' => !$row->trashed(),  // Disable if soft-deleted
                'showDetail' => Auth::user()?->can('view entity'),
                'detailHref' => route('entity.show', ['id' => $row->id]),
                'showEdit' => Auth::user()?->can('update entity'),
                'editHref' => route('entity.edit', ['id' => $row->id]),
                'showDelete' => Auth::user()?->can('delete entity'),
                'deleteDispatchEvent' => 'entity.delete',
            ])),

        // ... other columns
    ];
}
```

**Key Points:**
- Actions column ALWAYS first
- Column name: `'Actions'` (plural), never `'Action'`
- Use `datatable-action` component for consistency
- Check permissions before showing buttons
- Use `BooleanColumn` for boolean fields (is_active, is_default)
- Never use inline HTML, `->html()`, or custom view files

**Custom Actions** (e.g., delivery creation):
```php
'showDeliveryButton' => Auth::user()?->can('create sales delivery'),
'deliveryButtonHref' => route('shp.sales.delivery.create', ['order' => $row->id]),
```

### 13. Code Generation

- **Orders**: `TYPE/YYMM/####` - `CodeGeneratorHelper::generateOrderNumber('SO')`
- **Deliveries**: `DLV/TYPE/YYMM/####` - `CodeGeneratorHelper::generateDeliveryNumber('SO')`
- **Billing**: `BILL/TYPE/YYMM/####` - `CodeGeneratorHelper::generateBillingNumber('SO')`
- **Items**: Use `ItemCodeGeneratorHelper` for SKU generation

### 14. Production Workflow

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
- [ ] **Routes added to `routes/web.php`**
- [ ] **Navigation added to `sidebar.blade.php` (keep items sorted A-Z within each group)**
- [ ] **Modal Create/Edit: Authorization in `openModal()` NOT `mount()`. Action buttons wrapped in `@can()`. DataTable columns check permissions**
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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.1
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/flux-pro (FLUXUI_PRO) - v2
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== fluxui-pro/core rules ===

## Flux UI Pro

- This project is using the Pro version of Flux UI. It has full access to the free components and variants, as well as full access to the Pro components and variants.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted, UI component library for your Livewire applications. It's built using Tailwind CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use Laravel Boost's `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

<code-snippet name="Flux UI component usage example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>


### Available Components
This is correct as of Boost installation, but there may be additional components within the codebase.

<available-flux-components>
accordion, autocomplete, avatar, badge, brand, breadcrumbs, button, calendar, callout, card, chart, checkbox, command, composer, context, date-picker, dropdown, editor, field, file-upload, heading, icon, input, kanban, modal, navbar, otp-input, pagination, pillbox, popover, profile, radio, select, separator, skeleton, slider, switch, table, tabs, text, textarea, time-picker, toast, tooltip
</available-flux-components>


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== volt/core rules ===

## Livewire Volt

- This project uses Livewire Volt for interactivity within its pages. New pages requiring interactivity must also use Livewire Volt. There is documentation available for it.
- Make new Volt components using `php artisan make:volt [name] [--test] [--pest]`
- Volt is a **class-based** and **functional** API for Livewire that supports single-file components, allowing a component's PHP logic and Blade templates to co-exist in the same file
- Livewire Volt allows PHP logic and Blade templates in one file. Components use the `@volt` directive.
- You must check existing Volt components to determine if they're functional or class based. If you can't detect that, ask the user which they prefer before writing a Volt component.

### Volt Functional Component Example

<code-snippet name="Volt Functional Component Example" lang="php">
@volt
<?php
use function Livewire\Volt\{state, computed};

state(['count' => 0]);

$increment = fn () => $this->count++;
$decrement = fn () => $this->count--;

$double = computed(fn () => $this->count * 2);
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <h2>Double: {{ $this->double }}</h2>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
@endvolt
</code-snippet>


### Volt Class Based Component Example
To get started, define an anonymous class that extends Livewire\Volt\Component. Within the class, you may utilize all of the features of Livewire using traditional Livewire syntax:


<code-snippet name="Volt Class-based Volt Component Example" lang="php">
use Livewire\Volt\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
} ?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
</code-snippet>


### Testing Volt & Volt Components
- Use the existing directory for tests if it already exists. Otherwise, fallback to `tests/Feature/Volt`.

<code-snippet name="Livewire Test Example" lang="php">
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
</code-snippet>


<code-snippet name="Volt Component Test Using Pest" lang="php">
declare(strict_types=1);

use App\Models\{User, Product};
use Livewire\Volt\Volt;

test('product form creates product', function () {
    $user = User::factory()->create();

    Volt::test('pages.products.create')
        ->actingAs($user)
        ->set('form.name', 'Test Product')
        ->set('form.description', 'Test Description')
        ->set('form.price', 99.99)
        ->call('create')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});
</code-snippet>


### Common Patterns


<code-snippet name="CRUD With Volt" lang="php">
<?php

use App\Models\Product;
use function Livewire\Volt\{state, computed};

state(['editing' => null, 'search' => '']);

$products = computed(fn() => Product::when($this->search,
    fn($q) => $q->where('name', 'like', "%{$this->search}%")
)->get());

$edit = fn(Product $product) => $this->editing = $product->id;
$delete = fn(Product $product) => $product->delete();

?>

<!-- HTML / UI Here -->
</code-snippet>

<code-snippet name="Real-Time Search With Volt" lang="php">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />
</code-snippet>

<code-snippet name="Loading States With Volt" lang="php">
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>
