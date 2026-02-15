# Flux UI Components

## Component Philosophy

**ALWAYS use Flux components** - no custom HTML/CSS unless absolutely necessary.

**CRITICAL**: Read `docs/flux/components/{component-name}.md` BEFORE using any component.

## Quick Reference

| Component | Use Case | Key Pattern |
|-----------|----------|-------------|
| Button | Actions | `variant="primary\|danger\|ghost\|outline\|filled\|subtle"` |
| Badge | Status labels | Colors: zinc, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose |
| Switch | Single boolean | For `is_active`, `is_default` |
| Checkbox | Multiple choices | Wrap 2+ in `<flux:checkbox.group>` |
| Date Picker | Date input | Use `<flux:date-picker>`, NEVER `<flux:input type="date">` |
| Modal | Dialogs | `$this->modal('name')->show()` / `->close()` |
| Callout | Alerts | Colors: amber=warning, red=error, green=success, blue=info |
| Input/Textarea | Form fields | `label="..."` and `badge="Required"` attributes |

## Button Variants

```blade
<flux:button variant="primary">Save</flux:button>
<flux:button variant="danger">Delete</flux:button>
<flux:button variant="ghost">Cancel</flux:button>
<flux:button variant="outline">Edit</flux:button>
```

## Modal Pattern

```php
// Show modal
public function openModal()
{
    $this->modal('create-entity')->show();
}

// Close modal
public function store()
{
    // ... save logic
    $this->modal('create-entity')->close();
}
```

```blade
<flux:modal name="create-entity">
    <flux:heading>Create Entity</flux:heading>
    
    <form wire:submit="store">
        <flux:input wire:model="inputs.name" label="Name" />
        
        <div class="flex gap-2 mt-6">
            <flux:spacer/>
            <flux:button type="submit" variant="primary">Save</flux:button>
            <flux:button variant="ghost" x-on:click="$flux.modal('create-entity').close()">Cancel</flux:button>
        </div>
    </form>
</flux:modal>
```

## Table Patterns

### Numeric Columns (aligned digits)
```blade
<flux:table>
    <flux:columns>
        <flux:column class="text-center">Price</flux:column>
        <flux:column class="text-center">Quantity</flux:column>
        <flux:column class="text-center">Total</flux:column>
    </flux:columns>
    
    <flux:rows>
        @foreach($items as $item)
        <flux:row>
            <flux:cell class="text-right tabular-nums">{{ number_format($item->price, 2) }}</flux:cell>
            <flux:cell class="text-right tabular-nums">{{ number_format($item->quantity, 2) }}</flux:cell>
            <flux:cell class="text-right tabular-nums">{{ number_format($item->total, 2) }}</flux:cell>
        </flux:row>
        @endforeach
    </flux:rows>
</flux:table>
```

**Pattern**:
- **Headers**: Always `text-center` for better aesthetics
- **Numeric cells**: Always `text-right tabular-nums` for aligned digits
- **Why `tabular-nums`**: Ensures digits have equal width for perfect alignment

### Notes Column (preserve line breaks)
```blade
<flux:table>
    <flux:columns>
        <flux:column>Name</flux:column>
        <flux:column class="max-w-md whitespace-pre-line break-words">Notes</flux:column>
    </flux:columns>
    
    <flux:rows>
        @foreach($items as $item)
        <flux:row>
            <flux:cell>{{ $item->name }}</flux:cell>
            <flux:cell class="max-w-md whitespace-pre-line break-words">{{ $item->notes }}</flux:cell>
        </flux:row>
        @endforeach
    </flux:rows>
</flux:table>
```

### Action Column (button alignment)
```blade
<flux:column class="align-middle">Actions</flux:column>

<flux:cell class="align-middle">
    <div class="flex items-center gap-3">
        <flux:button size="sm">Edit</flux:button>
        <flux:button size="sm" variant="danger">Delete</flux:button>
    </div>
</flux:cell>
```

**NEVER add background colors to table rows/cells**.

## Date Picker

```blade
{{-- ✅ CORRECT --}}
<flux:date-picker wire:model="inputs.order_date" label="Order Date" />

{{-- ❌ WRONG --}}
<flux:input type="date" wire:model="inputs.order_date" label="Order Date" />
```

## Callout (Alert Messages)

```blade
<flux:callout variant="amber">Warning message</flux:callout>
<flux:callout variant="red">Error message</flux:callout>
<flux:callout variant="green">Success message</flux:callout>
<flux:callout variant="blue">Info message</flux:callout>
```

## Switch vs Checkbox

```blade
{{-- Single boolean toggle --}}
<flux:switch wire:model="inputs.is_active" label="Active" />

{{-- Multiple choices --}}
<flux:checkbox.group>
    <flux:checkbox wire:model="inputs.permissions" value="create" label="Create" />
    <flux:checkbox wire:model="inputs.permissions" value="read" label="Read" />
    <flux:checkbox wire:model="inputs.permissions" value="update" label="Update" />
</flux:checkbox.group>
```

## Input Attributes

```blade
<flux:input 
    wire:model="inputs.code" 
    label="Code" 
    badge="Required"
    placeholder="Enter code"
    maxlength="50"
/>

<flux:textarea 
    wire:model="inputs.remarks" 
    label="Remarks"
    rows="3"
/>
```

## Documentation First

Before using any component not listed here, **read the docs**:

```
docs/flux/components/{component-name}.md
```

Examples, full API, and usage patterns are documented per component.
