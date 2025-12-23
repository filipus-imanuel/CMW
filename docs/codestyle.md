# SHP ERP Code Style Guide

**Version**: 1.0.0  
**Last Updated**: 2025-12-23

---

## Table of Contents

1. [Livewire Components](#livewire-components)
2. [Master Data Pattern](#master-data-pattern)
3. [Transaction Pattern](#transaction-pattern)
4. [DataTable Components](#datatable-components)
5. [View Templates](#view-templates)
6. [Naming Conventions](#naming-conventions)
7. [Event System](#event-system)
8. [Validation](#validation)
9. [File Uploads](#file-uploads)
10. [Permissions](#permissions)

---

## Livewire Components

### File Structure

```
app/Livewire/Shp/{Module}/{Entity}/
├── Create.php           # Form creation (modal-based for master, page for transaction)
├── Edit.php             # Form editing
├── Index.php            # List page wrapper
├── IndexDataTable.php   # DataTable configuration (Rappasoft)
├── Show.php             # Detail view
└── Search.php           # Search modal component (optional)
```

### Component Class Template

```php
<?php

namespace App\Livewire\Shp\{Module}\{Entity};

use Livewire\Component;
use Livewire\Attributes\{Layout, Title, On, Computed};
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\{Auth, DB};
use App\Models\SHP\{Module}\{Entity};
use Flux;

#[Layout('components.layouts.shp')]
#[Title('{Entity Name}')]
class Create extends Component
{
    use WithFileUploads; // Only when handling file uploads
    
    // ══════════════════════════════════════════════════════════════
    // PROPERTIES
    // ══════════════════════════════════════════════════════════════
    
    // Model reference (for edit/show)
    public ?{Entity} $entity = null;
    
    // Form inputs - use snake_case matching database columns
    public $name;
    public $code;
    public $description;
    public $is_active = true;
    
    // File uploads
    public $photo_file;
    
    // Select options
    public $categories = [];
    
    // ══════════════════════════════════════════════════════════════
    // LIFECYCLE HOOKS
    // ══════════════════════════════════════════════════════════════
    
    public function mount($id = null)
    {
        $this->authorize('create {entity}');
        $this->handlePopulateInputs();
    }
    
    public function boot()
    {
        // Runs on every request
    }
    
    // ══════════════════════════════════════════════════════════════
    // VALIDATION
    // ══════════════════════════════════════════════════════════════
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:table_name,code',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'photo_file' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'code.unique' => 'Kode sudah digunakan.',
        ];
    }
    
    // ══════════════════════════════════════════════════════════════
    // COMPUTED PROPERTIES
    // ══════════════════════════════════════════════════════════════
    
    #[Computed]
    public function totalAmount()
    {
        return collect($this->items)->sum('amount');
    }
    
    // ══════════════════════════════════════════════════════════════
    // ACTIONS
    // ══════════════════════════════════════════════════════════════
    
    public function save()
    {
        $this->authorize('create {entity}');
        $validated = $this->validate();
        
        DB::transaction(function () use ($validated) {
            // Prepare data
            $data = [
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'],
                'created_by' => Auth::id(),
            ];
            
            // Handle file upload
            if ($this->photo_file) {
                $data['photo'] = FileUploadHelper::uploadFile(
                    $this->photo_file,
                    '{Module}/{Entity}'
                );
            }
            
            // Create record
            $entity = {Entity}::create($data);
            
            // Success feedback
            Flux::toast(
                '{Entity} berhasil dibuat',
                variant: 'success',
                position: 'top-end'
            );
            
            // Dispatch event & close modal
            $this->dispatch('shp.{module}.{entity}.refresh');
            $this->modal('create-{entity}')->close();
        });
    }
    
    // ══════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ══════════════════════════════════════════════════════════════
    
    private function handlePopulateInputs()
    {
        $this->categories = HandlingPopulateHelper::handlePopulateCategories();
    }
    
    private function resetForm()
    {
        $this->reset(['name', 'code', 'description', 'photo_file']);
        $this->is_active = true;
        $this->resetValidation();
    }
    
    // ══════════════════════════════════════════════════════════════
    // EVENT LISTENERS
    // ══════════════════════════════════════════════════════════════
    
    #[On('shp.{module}.{entity}.create.open')]
    public function openModal()
    {
        $this->resetForm();
        $this->modal('create-{entity}')->show();
    }
    
    // ══════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════
    
    public function render()
    {
        return view('livewire.shp.{module}.{entity}.create');
    }
}
```

---

## Master Data Pattern

Master data components use **modal-based** forms for create/edit operations.

### Create.php

```php
<?php

namespace App\Livewire\Shp\Master\ItemCategory;

use Livewire\Component;
use Livewire\Attributes\{Layout, Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use App\Models\SHP\Master\ItemCategory;
use Flux;

#[Layout('components.layouts.shp')]
#[Title('Item Category')]
class Create extends Component
{
    public $name;
    public $code;
    public $description;
    public $is_active = true;
    
    public function mount()
    {
        $this->authorize('create item category');
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:item_categories,code',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
    
    public function save()
    {
        $this->authorize('create item category');
        $validated = $this->validate();
        
        DB::transaction(function () use ($validated) {
            ItemCategory::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'],
                'created_by' => Auth::id(),
            ]);
            
            Flux::toast('Item Category berhasil dibuat', variant: 'success', position: 'top-end');
            $this->dispatch('shp.master.item-category.refresh');
            $this->modal('create-item-category')->close();
        });
    }
    
    #[On('shp.master.item-category.create.open')]
    public function openModal()
    {
        $this->reset(['name', 'code', 'description']);
        $this->is_active = true;
        $this->resetValidation();
        $this->modal('create-item-category')->show();
    }
    
    public function render()
    {
        return view('livewire.shp.master.item-category.create');
    }
}
```

### Edit.php

```php
<?php

namespace App\Livewire\Shp\Master\ItemCategory;

use Livewire\Component;
use Livewire\Attributes\{Layout, Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use App\Models\SHP\Master\ItemCategory;
use Flux;

#[Layout('components.layouts.shp')]
#[Title('Edit Item Category')]
class Edit extends Component
{
    public ?ItemCategory $category = null;
    
    public $name;
    public $code;
    public $description;
    public $is_active;
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:item_categories,code,' . $this->category?->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
    
    public function update()
    {
        $this->authorize('edit item category');
        $validated = $this->validate();
        
        DB::transaction(function () use ($validated) {
            $this->category->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'],
                'updated_by' => Auth::id(),
            ]);
            
            Flux::toast('Item Category berhasil diupdate', variant: 'success', position: 'top-end');
            $this->dispatch('shp.master.item-category.refresh');
            $this->modal('edit-item-category')->close();
        });
    }
    
    #[On('shp.master.item-category.edit.open')]
    public function openModal($id)
    {
        $this->authorize('edit item category');
        $this->resetValidation();
        
        $this->category = ItemCategory::findOrFail($id);
        
        // Populate form fields
        $this->name = $this->category->name;
        $this->code = $this->category->code;
        $this->description = $this->category->description;
        $this->is_active = $this->category->is_active;
        
        $this->modal('edit-item-category')->show();
    }
    
    public function render()
    {
        return view('livewire.shp.master.item-category.edit');
    }
}
```

### Index.php

```php
<?php

namespace App\Livewire\Shp\Master\ItemCategory;

use Livewire\Component;
use Livewire\Attributes\{Layout, Title, On};

#[Layout('components.layouts.shp')]
#[Title('Item Categories')]
class Index extends Component
{
    #[On('shp.master.item-category.refresh')]
    public function refresh()
    {
        // This method exists to trigger component refresh
        // DataTable will auto-refresh when this is called
    }
    
    public function render()
    {
        return view('livewire.shp.master.item-category.index');
    }
}
```

---

## Transaction Pattern

Transaction components use **page-based** forms (separate pages for create/edit).

### Key Differences from Master Data

| Aspect | Master Data | Transaction |
|--------|-------------|-------------|
| Form Type | Modal | Full Page |
| Navigation | Stay on index | Navigate to form page |
| Complexity | Simple fields | Header + Details + Costs |
| Status | Usually just active/inactive | Draft → Process → Complete |

### Transaction Create/Edit Pattern

```php
<?php

namespace App\Livewire\Shp\Sales\Order;

use Livewire\Component;
use Livewire\Attributes\{Layout, Title, On};
use Illuminate\Support\Facades\{Auth, DB};
use App\Models\SHP\Transaction\SalesOrder;
use App\Helpers\TransactionHelper;
use Flux;

#[Layout('components.layouts.shp')]
#[Title('Create Sales Order')]
class Create extends Component
{
    // Header fields
    public $customer_id;
    public $order_date;
    public $notes;
    
    // Details (array of items)
    public $details = [];
    
    // Costs
    public $costs = [];
    
    public function mount()
    {
        $this->authorize('create sales order');
        $this->order_date = now()->format('Y-m-d');
        $this->handlePopulateInputs();
    }
    
    public function rules()
    {
        return [
            'customer_id' => 'required|exists:partners,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.item_id' => 'required|exists:items,id',
            'details.*.qty' => 'required|numeric|min:0.01',
            'details.*.price' => 'required|numeric|min:0',
        ];
    }
    
    public function save()
    {
        $this->authorize('create sales order');
        $validated = $this->validate();
        
        DB::transaction(function () use ($validated) {
            // Create header
            $order = SalesOrder::create([
                'order_number' => CodeGeneratorHelper::generateOrderNumber('SO'),
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'notes' => $validated['notes'],
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);
            
            // Create details
            foreach ($this->details as $detail) {
                $order->details()->create([
                    'item_id' => $detail['item_id'],
                    'qty' => sanitize_numeric($detail['qty']),
                    'price' => sanitize_numeric($detail['price']),
                    'created_by' => Auth::id(),
                ]);
            }
            
            // Recalculate totals
            TransactionHelper::updateSalesOrderHeaderTotals($order);
            
            Flux::toast('Sales Order berhasil dibuat', variant: 'success', position: 'top-end');
            
            $this->redirectRoute('sales.order.show', ['id' => $order->id], navigate: true);
        });
    }
    
    // Detail management
    public function addDetail()
    {
        $this->details[] = [
            'item_id' => null,
            'item_name' => '',
            'qty' => 1,
            'price' => 0,
            'amount' => 0,
        ];
    }
    
    public function removeDetail($index)
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
    }
    
    public function render()
    {
        return view('livewire.shp.sales.order.create');
    }
}
```

### Status Guard Pattern

```php
public function mount($id)
{
    $this->authorize('edit sales order');
    $this->order = SalesOrder::findOrFail($id);
    
    // Status guard - redirect if not editable
    if ($this->order->status !== 'DRAFT') {
        Flux::toast('Order tidak dapat diedit', variant: 'danger', position: 'top-end');
        $this->redirectRoute('sales.order.show', ['id' => $id], navigate: true);
        return;
    }
    
    $this->handlePopulateInputs();
}
```

---

## DataTable Components

### IndexDataTable.php

```php
<?php

namespace App\Livewire\Shp\Master\ItemCategory;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\SHP\Master\ItemCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IndexDataTable extends DataTableComponent
{
    protected $model = ItemCategory::class;
    
    protected $listeners = [
        'shp.master.item-category.refresh' => '$refresh',
    ];
    
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Cari item category...')
            ->setColumnSelectEnabled()
            ->setEmptyMessage('Tidak ada data ditemukan');
    }
    
    public function builder(): Builder
    {
        return ItemCategory::query()
            ->with(['createdBy', 'updatedBy']);
    }
    
    public function columns(): array
    {
        return [
            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),
                
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),
                
            Column::make('Status', 'is_active')
                ->sortable()
                ->format(fn ($value) => $value
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>'
                )->html(),
                
            Column::make('Created At', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->format('d M Y H:i')),
                
            Column::make('Actions')
                ->label(fn ($row) => $this->renderActions($row))
                ->html(),
        ];
    }
    
    private function renderActions($row): string
    {
        $actions = '<div class="flex items-center gap-2">';
        
        if (Auth::user()->can('view item category')) {
            $actions .= '<button wire:click="$dispatch(\'shp.master.item-category.show.open\', { id: ' . $row->id . ' })" class="text-gray-600 hover:text-gray-800" title="View"><flux:icon.eye class="size-5" /></button>';
        }
        
        if (Auth::user()->can('edit item category')) {
            $actions .= '<button wire:click="$dispatch(\'shp.master.item-category.edit.open\', { id: ' . $row->id . ' })" class="text-blue-600 hover:text-blue-800" title="Edit"><flux:icon.pencil class="size-5" /></button>';
        }
        
        if (Auth::user()->can('delete item category')) {
            $actions .= '<button wire:click="delete(' . $row->id . ')" wire:confirm="Apakah Anda yakin ingin menghapus data ini?" class="text-red-600 hover:text-red-800" title="Delete"><flux:icon.trash class="size-5" /></button>';
        }
        
        $actions .= '</div>';
        
        return $actions;
    }
    
    public function delete($id)
    {
        $this->authorize('delete item category');
        
        DB::transaction(function () use ($id) {
            $category = ItemCategory::findOrFail($id);
            $category->update(['deleted_by' => Auth::id()]);
            $category->delete();
            
            Flux::toast('Item Category berhasil dihapus', variant: 'success', position: 'top-end');
        });
    }
}
```

### ⚠️ DataTable Restrictions

**CRITICAL**: Di dalam `Column::format()` atau `->label()`:

- ✅ **BOLEH**: Flux icons (`<flux:icon.eye>`, `<flux:icon.pencil>`, `<flux:icon.trash>`)
- ✅ **BOLEH**: Pure Tailwind HTML untuk badges/buttons
- ❌ **TIDAK BOLEH**: Flux components lainnya (`<flux:badge>`, `<flux:button>`)
- ❌ **TIDAK BOLEH**: Livewire directives kompleks

```php
// ✅ CORRECT - Pure Tailwind badge
->format(fn ($value) => $value
    ? '<span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">Active</span>'
    : '<span class="px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">Inactive</span>'
)

// ❌ WRONG - Flux component
->format(fn ($value) => '<flux:badge color="green">Active</flux:badge>')
```

---

## View Templates

### Modal Form (Master Data)

```blade
{{-- create.blade.php --}}
<flux:modal name="create-item-category" class="w-full max-w-2xl">
    <form wire:submit="save">
        <div class="space-y-6">
            <flux:heading size="lg">Create Item Category</flux:heading>
            
            <div class="space-y-6">
                <flux:input 
                    wire:model="code" 
                    label="Code" 
                    badge="Required"
                    placeholder="Masukkan kode"
                />
                
                <flux:input 
                    wire:model="name" 
                    label="Name" 
                    badge="Required"
                    placeholder="Masukkan nama"
                />
                
                <flux:textarea 
                    wire:model="description" 
                    label="Description"
                    placeholder="Masukkan deskripsi"
                    rows="3"
                />
                
                <flux:checkbox 
                    wire:model="is_active" 
                    label="Active"
                />
            </div>
            
            <div class="flex">
                <flux:spacer />
                <flux:button 
                    type="button" 
                    variant="ghost" 
                    wire:click="$parent.modal('create-item-category').close()"
                >
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
```

### Index Page

```blade
{{-- index.blade.php --}}
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Item Categories</flux:heading>
        
        @can('create item category')
        <flux:button 
            wire:click="$dispatch('shp.master.item-category.create.open')"
            variant="primary"
            icon="plus"
        >
            Create Category
        </flux:button>
        @endcan
    </div>
    
    {{-- DataTable --}}
    <flux:card>
        <livewire:shp.master.item-category.index-data-table />
    </flux:card>
    
    {{-- Modals --}}
    <livewire:shp.master.item-category.create />
    <livewire:shp.master.item-category.edit />
</div>
```

### Detail/Show Page

```blade
{{-- show.blade.php --}}
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $order->order_number }}</flux:heading>
            <flux:subheading>Sales Order Details</flux:subheading>
        </div>
        
        <div class="flex items-center gap-2">
            @can('edit sales order')
            @if($order->status === 'DRAFT')
            <flux:button 
                wire:navigate 
                href="{{ route('sales.order.edit', $order->id) }}"
                variant="outline"
                icon="pencil"
            >
                Edit
            </flux:button>
            @endif
            @endcan
            
            <flux:button 
                wire:navigate 
                href="{{ route('sales.order.index') }}"
                variant="ghost"
            >
                Back
            </flux:button>
        </div>
    </div>
    
    {{-- Status Badge --}}
    <flux:badge color="{{ match($order->status) {
        'DRAFT' => 'zinc',
        'CONFIRMED' => 'blue',
        'PROCESSING' => 'amber',
        'COMPLETED' => 'green',
        'CANCELLED' => 'red',
        default => 'zinc'
    } }}" size="lg">
        {{ $order->status }}
    </flux:badge>
    
    {{-- Info Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <flux:card>
            <flux:card.header>
                <flux:heading size="lg">Order Information</flux:heading>
            </flux:card.header>
            <flux:card.body>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Customer</dt>
                        <dd class="mt-1">{{ $order->customer->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                        <dd class="mt-1">{{ $order->order_date->format('d M Y') }}</dd>
                    </div>
                </dl>
            </flux:card.body>
        </flux:card>
    </div>
    
    {{-- Details Table --}}
    <flux:card>
        <flux:card.header>
            <flux:heading size="lg">Order Items</flux:heading>
        </flux:card.header>
        <flux:card.body class="p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Item</flux:table.column>
                    <flux:table.column class="text-right">Qty</flux:table.column>
                    <flux:table.column class="text-right">Price</flux:table.column>
                    <flux:table.column class="text-right">Amount</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($order->details as $detail)
                    <flux:table.row>
                        <flux:table.cell>{{ $detail->item->name }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ format_number($detail->qty) }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ format_number($detail->price) }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ format_number($detail->amount) }}</flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card.body>
    </flux:card>
</div>
```

---

## Naming Conventions

### Files & Classes

| Type | Convention | Example |
|------|------------|---------|
| Component Class | PascalCase | `Create.php`, `IndexDataTable.php` |
| Model | PascalCase Singular | `ItemCategory.php`, `SalesOrder.php` |
| View | kebab-case | `create.blade.php`, `index-data-table.blade.php` |
| Migration | snake_case with timestamp | `2025_01_01_000000_create_items_table.php` |

### Properties & Variables

| Type | Convention | Example |
|------|------------|---------|
| Public properties | snake_case | `$customer_id`, `$order_date` |
| Private properties | snake_case | `$_cache_key` |
| Methods | camelCase | `handlePopulateInputs()`, `calculateTotal()` |
| Constants | UPPER_SNAKE | `STATUS_DRAFT`, `MAX_ITEMS` |

### Database

| Type | Convention | Example |
|------|------------|---------|
| Tables | snake_case plural | `item_categories`, `sales_orders` |
| Columns | snake_case | `customer_id`, `created_at` |
| Foreign keys | singular_table_id | `customer_id`, `item_id` |
| Pivot tables | alphabetical singular | `item_warehouse`, `role_user` |

### Events

Pattern: `shp.{module}.{entity}.{action}`

```php
// Examples
'shp.master.item-category.refresh'
'shp.master.item-category.create.open'
'shp.master.item-category.edit.open'
'shp.sales.order.refresh'
'shp.sales.order.detail.added'
'shp.production.refresh_intermediates'
```

### Permissions

Pattern: `{action} {singular entity}`

```php
// Examples
'create item category'
'edit item category'
'delete item category'
'view sales order'
'approve sales order'
'cancel production'
```

---

## Event System

### Dispatching Events

```php
// After create/update/delete - refresh list
$this->dispatch('shp.master.item-category.refresh');

// Open modal
$this->dispatch('shp.master.item-category.create.open');
$this->dispatch('shp.master.item-category.edit.open', id: $id);

// With multiple parameters
$this->dispatch('shp.sales.order.item.selected', [
    'item_id' => $itemId,
    'index' => $index,
]);
```

### Listening to Events

```php
// Using attribute
#[On('shp.master.item-category.refresh')]
public function refresh() {}

#[On('shp.master.item-category.edit.open')]
public function openModal($id) {}

// Using $listeners property (DataTable)
protected $listeners = [
    'shp.master.item-category.refresh' => '$refresh',
];
```

### Event Flow Pattern

```
User Action → Component Method → DB Transaction → Toast → Dispatch Event → Listeners Refresh
```

---

## Validation

### Rules Method Pattern

```php
public function rules()
{
    return [
        // Required string
        'name' => 'required|string|max:255',
        
        // Unique with ignore for edit
        'code' => 'required|string|max:50|unique:table,code,' . $this->entity?->id,
        
        // Numeric with sanitization
        'qty' => 'required|numeric|min:0.01',
        'price' => 'required|numeric|min:0',
        
        // Date
        'order_date' => 'required|date',
        
        // Foreign key
        'customer_id' => 'required|exists:partners,id',
        
        // Array validation
        'details' => 'required|array|min:1',
        'details.*.item_id' => 'required|exists:items,id',
        'details.*.qty' => 'required|numeric|min:0.01',
        
        // File
        'photo_file' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        
        // Conditional
        'discount_percent' => 'required_if:discount_type,percent|numeric|min:0|max:100',
    ];
}
```

### Custom Messages

```php
public function messages()
{
    return [
        'name.required' => 'Nama wajib diisi.',
        'code.unique' => 'Kode sudah digunakan.',
        'details.required' => 'Minimal harus ada 1 item.',
        'details.*.qty.min' => 'Qty item harus lebih dari 0.',
    ];
}
```

### Real-time Validation

```php
// Validate on property update
public function updated($property)
{
    $this->validateOnly($property);
}

// Validate specific fields
public function updatedCustomerId($value)
{
    $this->validateOnly('customer_id');
    $this->loadCustomerData($value);
}
```

---

## File Uploads

### Setup

```php
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads; // CRITICAL: Always add this trait!
    
    public $photo_file;
    public $document_file;
}
```

### Validation

```php
public function rules()
{
    return [
        'photo_file' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // 2MB
        'document_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB
    ];
}
```

### Upload to S3

```php
use App\Helpers\FileUploadHelper;

public function save()
{
    $validated = $this->validate();
    
    DB::transaction(function () use ($validated) {
        $data = [...];
        
        // Upload file if exists
        if ($this->photo_file) {
            $data['photo'] = FileUploadHelper::uploadFile(
                $this->photo_file,
                'Master/ItemCategory' // Path in S3
            );
        }
        
        Model::create($data);
    });
}
```

### View Template

```blade
<flux:file 
    wire:model="photo_file" 
    label="Photo"
    accept="image/*"
/>

{{-- Preview --}}
@if($photo_file)
<div class="mt-2">
    <img src="{{ $photo_file->temporaryUrl() }}" class="w-32 h-32 object-cover rounded">
</div>
@endif
```

---

## Permissions

### Adding New Permissions

1. **Update PermissionHelper**

```php
// app/Helpers/SHP/PermissionHelper.php

public static function master(): array
{
    return [
        'item category' => ['view', 'create', 'edit', 'delete'],
        'item' => ['view', 'create', 'edit', 'delete', 'export'],
        // Add new entity here
    ];
}
```

2. **Sync Permissions**

```bash
php artisan permission:sync
```

### Double Gate Pattern

Always implement both UI gate and server-side authorization:

```php
// View (UI Gate)
@can('create item category')
<flux:button wire:click="$dispatch('shp.master.item-category.create.open')">
    Create
</flux:button>
@endcan

// Component (Server Gate)
public function save()
{
    $this->authorize('create item category'); // MUST have this!
    // ... rest of logic
}
```

### Permission Check in DataTable

```php
private function renderActions($row): string
{
    $actions = '<div class="flex items-center gap-2">';
    
    if (Auth::user()->can('edit item category')) {
        $actions .= '...';
    }
    
    if (Auth::user()->can('delete item category')) {
        $actions .= '...';
    }
    
    $actions .= '</div>';
    
    return $actions;
}
```

---

## Checklist

### New Component Checklist

- [ ] Add `#[Layout]` and `#[Title]` attributes
- [ ] Implement `rules()` method for validation
- [ ] Add `$this->authorize()` in mount and action methods
- [ ] Wrap mutations in `DB::transaction()`
- [ ] Populate audit fields (`created_by`, `updated_by`, `deleted_by`)
- [ ] Add `Flux::toast()` for user feedback
- [ ] Dispatch events after state changes
- [ ] Use `WithFileUploads` trait if handling files

### New Master Data Checklist

- [ ] Create all component files (Create, Edit, Index, IndexDataTable)
- [ ] Create view files in `resources/views/livewire/shp/master/{entity}/`
- [ ] Add permissions in `PermissionHelper::master()`
- [ ] Run `php artisan permission:sync`
- [ ] Implement modal-based create/edit
- [ ] Add event listeners for refresh

### Pre-Commit Checklist

- [ ] No `dd()`, `dump()`, `var_dump()` calls
- [ ] All DB mutations wrapped in transactions
- [ ] Double gate implemented (UI + server)
- [ ] Events dispatched after mutations
- [ ] Toast notifications added
- [ ] File uploads have `WithFileUploads` trait
- [ ] Numeric inputs sanitized with `sanitize_numeric()`

---

## Quick Reference

### Toast Notifications

```php
Flux::toast('Success message', variant: 'success', position: 'top-end');
Flux::toast('Error message', variant: 'danger', position: 'top-end');
Flux::toast('Warning message', variant: 'warning', position: 'top-end');
```

### Modal Control

```php
// Open modal
$this->modal('modal-name')->show();
Flux::modal('modal-name')->show(); // Alternative

// Close modal
$this->modal('modal-name')->close();
```

### Navigation

```php
// With Livewire navigate
$this->redirectRoute('route.name', ['id' => $id], navigate: true);

// In Blade
<flux:button wire:navigate href="{{ route('route.name') }}">Link</flux:button>
```

### Fresh Data After Update

```php
// Refresh model with relations
$this->order = $this->order->fresh(['details', 'customer', 'costs']);
```
