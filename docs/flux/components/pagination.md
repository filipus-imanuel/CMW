# Pagination Component (Flux Pro)

Display navigation buttons for paginated data with automatic result count display.

---

## Basic Usage

Standard paginator with page numbers and result count.

```blade
<!-- Controller/Component -->
$orders = Order::paginate(15);

<!-- Blade Template -->
<flux:pagination :paginator="$orders" />
```

**Output:**
- "Showing X to Y of Z results" text
- Numbered page buttons
- Previous/Next navigation
- Current page highlighted

---

## Simple Paginator

Use for large datasets where counting total results is expensive. Shows only Previous/Next buttons without page numbers.

```blade
<!-- Controller/Component -->
$orders = Order::simplePaginate(15);

<!-- Blade Template -->
<flux:pagination :paginator="$orders" />
```

**When to use:**
- Large datasets (millions of rows)
- Performance-critical queries
- COUNT(*) queries are slow
- Total count not needed

---

## Large Result Sets

Automatically adapts for numerous pages with intelligent ellipsis display.

```blade
<!-- Many pages (e.g., 1000 results) -->
$orders = Order::paginate(5);

<flux:pagination :paginator="$orders" />
```

**Behavior:**
- Shows first page (1)
- Shows current page window (e.g., 4-8)
- Shows last page (e.g., 200)
- Adds "..." ellipsis for gaps
- Prevents UI overflow

**Example display:** `1 2 3 4 5 ... 198 199 200`

---

## Livewire Integration

```php
<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;

class OrderIndex extends Component
{
    use WithPagination;
    
    public function render()
    {
        return view('livewire.order-index', [
            'orders' => Order::latest()->paginate(15),
        ]);
    }
}
```

```blade
<!-- livewire/order-index.blade.php -->
<div>
    <flux:table>
        <!-- Table content -->
    </flux:table>
    
    <flux:pagination :paginator="$orders" />
</div>
```

---

## With Table Component

Common pattern for data tables.

```blade
<div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
            <flux:table.column>Status</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($orders as $order)
                <flux:table.row>
                    <flux:table.cell>{{ $order->customer }}</flux:table.cell>
                    <flux:table.cell>{{ $order->amount }}</flux:table.cell>
                    <flux:table.cell>{{ $order->status }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$orders" class="mt-4" />
</div>
```

---

## API Reference

### flux:pagination

**Props:**
| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `paginator` | Paginator | Yes | Laravel paginator instance |

**Supported Paginators:**
- `Model::paginate()` - Standard paginator with page numbers
- `Model::simplePaginate()` - Simple Previous/Next only
- `Model::cursorPaginate()` - Cursor-based (displays as simple)

**Features:**
- Auto-detects paginator type
- Responsive design
- Accessibility compliant
- Livewire-aware (no page reload)

---

## Best Practices

✅ **DO:**
- Use standard paginator for UI-friendly datasets (<10k results)
- Use simple paginator for large datasets or performance concerns
- Place below tables/lists with spacing (`mt-4`)
- Use Livewire `WithPagination` trait for reactive updates

❌ **DON'T:**
- Count large tables unnecessarily (use `simplePaginate()`)
- Paginate without indexes on query columns
- Use custom pagination HTML (Flux handles it)

---

## Performance Tips

**Standard Paginator:**
```php
// Adds COUNT(*) query
Order::paginate(15); // 2 queries: COUNT + SELECT
```

**Simple Paginator:**
```php
// No COUNT query
Order::simplePaginate(15); // 1 query: SELECT only
```

**When COUNT(*) is slow:**
- Table has millions of rows
- Complex WHERE clauses
- Joins on large tables
- No covering indexes

**Solution:** Use `simplePaginate()` and sacrifice total page count display.

---

**Reference:** https://fluxui.dev/components/pagination
