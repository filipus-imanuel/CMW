# DataTable Components (Rappasoft LaravelLivewireTables)

## Action Column Pattern (CRITICAL)

**Actions column ALWAYS first** in `columns()` array.

**Column name**: `'Actions'` (plural), never `'Action'`.

**Use standardized component**: `view('components.datatables.datatable-action', [...])`

```php
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelLivewireTables\Views\Column;

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
        
        // Other columns after Actions
        Column::make('Code', 'code')->sortable()->searchable(),
        Column::make('Name', 'name')->sortable()->searchable(),
        // ...
    ];
}
```

**Permission checks**: Always use `Auth::user()?->can()` with **null-safe operator** (`?->`).

## Custom Action Buttons

Add parameters to component, don't pass HTML strings:

```php
Column::make('Actions', 'id')
    ->format(fn ($value, $row) => view('components.datatables.datatable-action', [
        'rowId' => $row->id,
        'enable_this_row' => !$row->trashed(),
        'showDetail' => Auth::user()?->can('view sales order'),
        'detailHref' => route('shp.sales.order.show', ['id' => $row->id]),
        'showEdit' => Auth::user()?->can('update sales order'),
        'editHref' => route('shp.sales.order.edit', ['id' => $row->id]),
        'showDelete' => Auth::user()?->can('delete sales order'),
        'deleteDispatchEvent' => 'sales.order.delete',
        
        // Custom action (e.g., create delivery from order)
        'showDeliveryButton' => Auth::user()?->can('create sales delivery'),
        'deliveryButtonHref' => route('shp.sales.delivery.create', ['order' => $row->id]),
    ])),
```

## Component Restrictions (CRITICAL)

**NO Flux components in `Column::format()`** except icons.

**Flux icons ONLY work**: `<flux:icon.eye>`, `<flux:icon.pencil>`, `<flux:icon.trash>`

**Use pure Tailwind HTML** for badges/buttons in datatable columns:

```php
// ❌ WRONG - Flux components don't work in datatable format()
Column::make('Status', 'status')
    ->format(fn ($value) => "<flux:badge color='green'>$value</flux:badge>");

// ✅ CORRECT - Pure HTML + Tailwind
Column::make('Status', 'status')
    ->format(fn ($value) => "
        <span class='inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800'>
            $value
        </span>
    ");
```

## Wrap in Card (Index Views)

**ALWAYS wrap IndexDataTable in `<flux:card>`**:

```blade
<flux:card>
    <livewire:module.entity.index-data-table />
</flux:card>
```

## Relationship Columns with Filters

When displaying relationship data that's **filtered in `whereHas`**:

### Pattern Rules

1. **NEVER add constraints to eager load** - use simple `->with(['relation'])`
2. **Use base FK field** (e.g., `partner_id`), not nested path (e.g., `partner.name`)
3. **Format in `format()`** with null safety check
4. **Custom searchable** for relationship fields

```php
// ❌ WRONG - constraint on eager load causes null
public function builder(): Builder
{
    return Model::query()
        ->with(['partner' => fn($q) => $q->where('is_customer', true)])  // ❌ Filter here breaks it
        ->whereHas('partner', fn($q) => $q->where('is_customer', true));
}

// ✅ CORRECT - filter only in whereHas, simple eager load
public function builder(): Builder
{
    return Model::query()
        ->with(['partner'])  // Simple eager load, no constraints
        ->whereHas('partner', fn($q) => $q->where('is_customer', true));
}

// Column definition
Column::make('Customer', 'partner_id')  // Use FK field, not partner.name
    ->sortable()
    ->searchable(fn($query, $term) => 
        $query->orWhereHas('partner', fn($q) => 
            $q->where('name', 'like', "%{$term}%")
        )
    )
    ->format(fn($value, $row) => 
        $row->partner 
            ? "{$row->partner->name} ({$row->partner->code})" 
            : 'N/A'
    );
```

## Boolean Columns

Use `BooleanColumn` for boolean fields:

```php
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

BooleanColumn::make('Active', 'is_active')
    ->sortable(),
```

## Never Use

- ❌ Inline HTML strings with `->html()`
- ❌ Custom action view files (use standardized `datatable-action` component)
- ❌ Column name `'Action'` (singular) - always use `'Actions'` (plural)
- ❌ Actions column anywhere except first position
