# Flux Table Component

## Basic Structure
```blade
<flux:table>
    <flux:table.columns>
        <flux:table.column>Customer</flux:table.column>
        <flux:table.column>Date</flux:table.column>
        <flux:table.column>Status</flux:table.column>
        <flux:table.column>Amount</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        <flux:table.row>
            <flux:table.cell variant="strong">Lindsey Aminoff</flux:table.cell>
            <flux:table.cell>Jul 29, 10:45 AM</flux:table.cell>
            <flux:table.cell><flux:badge color="green" size="sm">Paid</flux:badge></flux:table.cell>
            <flux:table.cell variant="strong">$49.00</flux:table.cell>
        </flux:table.row>
        
        @forelse($items as $item)
            <flux:table.row :key="$item->id">
                <flux:table.cell>{{ $item->name }}</flux:table.cell>
                <!-- ... -->
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="4" class="text-center text-gray-500">
                    No items found.
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
```

## Pagination
```blade
<!-- $orders = \App\Models\Order::paginate(5) -->
<flux:table :paginate="$orders">
    <!-- ... -->
</flux:table>
```

## Sortable Columns
```blade
<flux:table>
    <flux:table.columns>
        <flux:table.column>Customer</flux:table.column>
        <flux:table.column sortable sorted direction="desc" wire:click="sort('date')">
            Date
        </flux:table.column>
        <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection">
            Amount
        </flux:table.column>
    </flux:table.columns>
    <!-- ... -->
</flux:table>
```

## Sticky Header
```blade
<!-- Set height on container -->
<flux:table container:class="max-h-80">
    <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
        <!-- columns -->
    </flux:table.columns>
    <!-- ... -->
</flux:table>
```

## Sticky Columns
```blade
<flux:table container:class="max-h-80">
    <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
        <flux:table.column sticky class="bg-white dark:bg-zinc-900">ID</flux:table.column>
        <!-- other columns -->
    </flux:table.columns>
    
    <flux:table.rows>
        @foreach($orders as $order)
            <flux:table.row :key="$order->id">
                <flux:table.cell sticky class="bg-white dark:bg-zinc-900">{{ $order->id }}</flux:table.cell>
                <!-- other cells -->
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
```

## Component Reference

### `<flux:table>`
**Props:**
- `paginate` - Laravel paginator instance for pagination
- `container:class` - Additional CSS classes for container (e.g., `max-h-80`)

### `<flux:table.columns>`
**Props:**
- `sticky` - Makes header row sticky when scrolling

### `<flux:table.column>`
**Props:**
- `align` - Content alignment: `start`, `center`, `end`
- `sortable` - Enables sorting functionality
- `sorted` - Indicates column is currently sorted
- `direction` - Sort direction: `asc`, `desc`
- `sticky` - Makes column sticky when scrolling

### `<flux:table.row>`
**Props:**
- `key` - Unique identifier (alias for `wire:key`)
- `sticky` - Makes row sticky when scrolling

### `<flux:table.cell>`
**Props:**
- `align` - Content alignment: `start`, `center`, `end`
- `variant` - Visual style: `default`, `strong`
- `sticky` - Makes cell sticky when scrolling

## Usage Guidelines
- Use `variant="strong"` for important cells (IDs, totals, primary data)
- Empty state: use `colspan` to span all columns with centered message
- Badges in cells: use `size="sm"` for compact display
- Always prefer Flux table over custom HTML tables
- **NEVER add background colors** to `<flux:table.row>` or `<flux:table.cell>` - styling is handled automatically

**Reference:** https://fluxui.dev/components/table
