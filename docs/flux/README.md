# Flux Pro UI Components - Complete Reference

This directory contains comprehensive documentation for all Flux Pro components used in the SHP ERP system.

## 📂 Directory Structure

```
docs/flux/
├── components/     # All 39 UI components with examples
├── guides/         # Usage patterns and best practices
├── layouts/        # Layout components (header, sidebar)
└── README.md       # This file
```

---

## 🧩 Components Reference

### Form Components
- **[input.md](components/input.md)** - Text inputs, numbers, emails, passwords
- **[textarea.md](components/textarea.md)** - Multi-line text input
- **[select.md](components/select.md)** - Dropdown selections
- **[checkbox.md](components/checkbox.md)** - Single/multiple checkboxes
- **[radio.md](components/radio.md)** - Radio button groups
- **[switch.md](components/switch.md)** - Toggle switches
- **[date-picker.md](components/date-picker.md)** - Date selection (use with Carbon)
- **[time-picker.md](components/time-picker.md)** - Time selection
- **[file-upload.md](components/file-upload.md)** - File upload component
- **[autocomplete.md](components/autocomplete.md)** - Auto-complete search
- **[editor.md](components/editor.md)** - Rich text editor
- **[field.md](components/field.md)** - Form field wrapper (legacy)
- **[pillbox.md](components/pillbox.md)** - Tag/pill input

### Navigation & Actions
- **[button.md](components/button.md)** - Buttons with variants (primary, danger, ghost, etc.)
- **[navbar.md](components/navbar.md)** - Navigation bar
- **[breadcrumbs.md](components/breadcrumbs.md)** - Breadcrumb navigation
- **[pagination.md](components/pagination.md)** - Page pagination
- **[tabs.md](components/tabs.md)** - Tab navigation
- **[dropdown.md](components/dropdown.md)** - Dropdown menus

### Display Components
- **[badge.md](components/badge.md)** - Status badges with colors
- **[card.md](components/card.md)** - Card containers
- **[table.md](components/table.md)** - Data tables
- **[callout.md](components/callout.md)** - Alert/information boxes
- **[accordion.md](components/accordion.md)** - Collapsible sections
- **[avatar.md](components/avatar.md)** - User avatars
- **[brand.md](components/brand.md)** - Brand/logo display
- **[chart.md](components/chart.md)** - Charts and graphs
- **[icon.md](components/icon.md)** - Icon system (Heroicons)
- **[profile.md](components/profile.md)** - User profile display

### Layout Components
- **[heading.md](components/heading.md)** - Headings with sizes
- **[text.md](components/text.md)** - Text display
- **[separator.md](components/separator.md)** - Divider lines

### Overlay Components
- **[modal.md](components/modal.md)** - Modal dialogs
- **[toast.md](components/toast.md)** - Toast notifications
- **[tooltip.md](components/tooltip.md)** - Tooltips
- **[popover.md](components/popover.md)** - Popover overlays
- **[context.md](components/context.md)** - Context menus
- **[command.md](components/command.md)** - Command palette

### Specialized
- **[calendar.md](components/calendar.md)** - Calendar display

---

## 📖 Guides

- **[principles.md](guides/principles.md)** - Core design principles
- **[patterns.md](guides/patterns.md)** - Common UI patterns (index, create, edit, show pages)
- **[theming.md](guides/theming.md)** - Customizing Flux themes
- **[dark-mode.md](guides/dark-mode.md)** - Dark mode implementation

---

## 🎨 Layouts

- **[header.md](layouts/header.md)** - Header layout component
- **[sidebar.md](layouts/sidebar.md)** - Sidebar navigation layout

---

## 🚨 Critical Rules

### 1. Always Use Flux Components
**NO custom HTML/CSS** unless absolutely necessary. Flux provides 39 components covering all common UI needs.

### 2. Valid Button Variants ONLY
Only these variants exist: `primary`, `danger`, `ghost`, `outline`, `filled`, `subtle`

**DON'T use**: `success`, `warning`, `info` (they will throw errors!)

### 3. Badge Colors
Available colors: zinc, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose

### 4. Date Pickers with Carbon
```php
// ✅ CORRECT
public ?Carbon $delivery_date = null;
```
```blade
<!-- ✅ CORRECT -->
<flux:date-picker wire:model="delivery_date" label="Delivery Date" />

<!-- ❌ WRONG -->
<flux:input type="date" wire:model="delivery_date" />
```

### 5. Tables - No Background Colors
```blade
<!-- ❌ WRONG -->
<flux:table.row class="bg-gray-50">  <!-- Don't do this! -->

<!-- ✅ CORRECT -->
<flux:table.row>  <!-- Flux handles styling -->
```

### 6. Form Fields - Modern Syntax
```blade
<!-- ✅ CORRECT - Modern -->
<flux:input wire:model="email" label="Email" badge="Required" />

<!-- ❌ WRONG - Old syntax -->
<flux:field>
    <flux:label badge="Required">Email</flux:label>
    <flux:input wire:model="email" />
</flux:field>
```

### 7. Modal Control
```php
// Open modal
Flux::modal('modal-name')->show();

// Close modal
$this->modal('modal-name')->close();

// DON'T use Livewire events for Flux modals
```

---

## 🔍 Quick Search Guide

Looking for a specific feature? Check these components:

| Need | Component |
|------|-----------|
| Text input | [input.md](components/input.md) |
| Dropdown/Select | [select.md](components/select.md) |
| Date selection | [date-picker.md](components/date-picker.md) |
| Button | [button.md](components/button.md) |
| Status indicator | [badge.md](components/badge.md) |
| Data display | [table.md](components/table.md) |
| Alert/Warning | [callout.md](components/callout.md) |
| Popup dialog | [modal.md](components/modal.md) |
| User feedback | [toast.md](components/toast.md) |
| File upload | [file-upload.md](components/file-upload.md) |
| Icons | [icon.md](components/icon.md) |
| Card container | [card.md](components/card.md) |

---

## 📚 External Resources

- **Official Flux Docs**: [https://fluxui.dev](https://fluxui.dev)
- **Heroicons**: [https://heroicons.com](https://heroicons.com) (Icon library used by Flux)

---

## 💡 Tips

1. **Always check the component docs** before creating custom HTML
2. **Use the guides** for page layout patterns (index, create, edit, show)
3. **Reference the principles** to understand Flux design philosophy
4. **Check examples** in each component doc for common use cases
5. **Follow the critical rules** to avoid runtime errors

---

**Last Updated**: 2025-11-23  
**Maintained By**: SHP Development Team
