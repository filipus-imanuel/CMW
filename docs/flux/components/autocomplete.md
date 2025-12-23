# Flux Autocomplete Component

## Overview
Enhance input fields with autocomplete suggestions that insert directly into the field. Use for text completion (states, cities, tags).

**⚠️ Important**: Autocomplete does NOT support separate value/label pairs. For ID-based selection (show name, store ID), use pillbox.md instead.

---

## Basic Usage

```blade
<flux:autocomplete wire:model="state" label="State of residence">
    <flux:autocomplete.item>Alabama</flux:autocomplete.item>
    <flux:autocomplete.item>Arkansas</flux:autocomplete.item>
    <flux:autocomplete.item>California</flux:autocomplete.item>
</flux:autocomplete>
```

---

## API Reference

### `<flux:autocomplete>` Props

| Prop | Type | Description |
|------|------|-------------|
| `wire:model` | `string` | Livewire property binding |
| `label` | `string` | Label text above input |
| `description` | `string` | Helper text below label |
| `placeholder` | `string` | Placeholder text |
| `size` | `"sm"`, `"xs"` | Input size variant |
| `variant` | `"filled"`, `"outline"` | Visual style (default: `outline`) |
| `disabled` | `boolean` | Disable user interaction |
| `readonly` | `boolean` | Read-only mode |
| `invalid` | `boolean` | Apply error styling |
| `icon` | `string` | Icon name at start |
| `icon:trailing` | `string` | Icon name at end |
| `clearable` | `boolean` | Show clear button |
| `copyable` | `boolean` | Show copy button |
| `kbd` | `string` | Keyboard shortcut hint |
| `mask` | `string` | Input mask pattern (e.g., `99/99/9999`) |
| `class:input` | `string` | CSS classes for input element |

### `<flux:autocomplete.item>` Props

| Prop | Type | Description |
|------|------|-------------|
| `disabled` | `boolean` | Prevent item selection |

---

## Common Patterns

### Dynamic Population
```blade
<flux:autocomplete wire:model="city" label="City" placeholder="Start typing...">
    @foreach($cities as $city)
        <flux:autocomplete.item>{{ $city }}</flux:autocomplete.item>
    @endforeach
</flux:autocomplete>
```

### With Icons & Clearable
```blade
<flux:autocomplete 
    wire:model="tag" 
    label="Add Tag" 
    icon="tag"
    size="sm"
    clearable>
    <flux:autocomplete.item>Laravel</flux:autocomplete.item>
    <flux:autocomplete.item>Livewire</flux:autocomplete.item>
    <flux:autocomplete.item>Flux</flux:autocomplete.item>
</flux:autocomplete>
```

### Disabled Items
```blade
<flux:autocomplete wire:model="country" label="Country">
    <flux:autocomplete.item>United States</flux:autocomplete.item>
    <flux:autocomplete.item disabled>Canada (Coming Soon)</flux:autocomplete.item>
    <flux:autocomplete.item>United Kingdom</flux:autocomplete.item>
</flux:autocomplete>
```

---

## Usage Guidelines

- **Text completion only**: Selected text is inserted directly into the input (no separate value storage)
- **Best for**: Addresses, tags, categories, simple text suggestions
- **NOT for**: User selection where you need to store IDs separately from display names
- **Dynamic items**: Populate from backend using `@foreach` loops
- **Validation**: Use standard Livewire validation rules on the wire:model property

---

## Autocomplete vs Combobox/Select

| Feature | Autocomplete | Combobox/Select |
|---------|-------------|-----------------|
| **Value storage** | Text only (WYSIWYG) | Separate value & label |
| **Use case** | Text completion | ID-based selection |
| **Example** | State names, tags | User selection (store user_id, show name) |
| **When to use** | Completing user typing | Selecting from predefined options |

---

**Reference**: [Flux Autocomplete Documentation](https://fluxui.dev/components/autocomplete)
