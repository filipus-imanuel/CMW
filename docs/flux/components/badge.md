# Flux Badge Component Reference

## Basic Usage

```blade
<flux:badge>Default</flux:badge>
<flux:badge color="green">Success</flux:badge>
<flux:badge color="red">Error</flux:badge>
```

## Sizes

```blade
<flux:badge size="sm">Small</flux:badge>
<flux:badge>Default</flux:badge>
<flux:badge size="lg">Large</flux:badge>
```

## Colors

**Available colors**: zinc, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose

```blade
<flux:badge color="zinc">Zinc</flux:badge>
<flux:badge color="green">Green</flux:badge>
<flux:badge color="blue">Blue</flux:badge>
<flux:badge color="red">Red</flux:badge>
<flux:badge color="amber">Amber</flux:badge>
```

## Variants

### Default Variant
Subtle with border (default)

```blade
<flux:badge color="green">Active</flux:badge>
```

### Solid Variant
Bold, high-contrast for emphasis

```blade
<flux:badge variant="solid" color="green">Success</flux:badge>
<flux:badge variant="solid" color="red">Error</flux:badge>
<flux:badge variant="solid" color="amber">Warning</flux:badge>
```

### Pill Variant
Fully rounded borders

```blade
<flux:badge variant="pill" icon="user">Users</flux:badge>
```

## Icons

```blade
<!-- Leading icon -->
<flux:badge icon="user-circle">Users</flux:badge>

<!-- Trailing icon -->
<flux:badge icon:trailing="video-camera">Videos</flux:badge>

<!-- With pill variant -->
<flux:badge variant="pill" icon="plus" size="lg">Add</flux:badge>
```

## With Close Button

```blade
<flux:badge>
    Admin
    <flux:badge.close />
</flux:badge>
```

## As Button

```blade
<flux:badge as="button" variant="pill" icon="plus" size="lg">
    Add Amount
</flux:badge>
```

## Inset (Alignment with Text)

Use when badges appear inline with text to fix spacing issues:

```blade
<flux:heading>
    Page builder 
    <flux:badge color="lime" inset="top bottom">New</flux:badge>
</flux:heading>
```

## Usage Guidelines

### Status Indicators
- `green` - Success, approved, completed, paid
- `amber` - Warning, pending, in progress
- `red` - Error, rejected, failed, overdue
- `blue` - Information, neutral status
- `zinc` - Default, inactive, archived

### Counts/Metrics
- Use `size="sm"` in tables or compact layouts
- Use `zinc` or `blue` for neutral counts
- Default size for standalone badges

### Emphasis
- Default variant for most cases (subtle)
- `variant="solid"` for important status requiring attention
- `variant="pill"` for tags or categories

## Props Reference

### `<flux:badge>`

| Prop | Description |
|------|-------------|
| `color` | Badge color. Options: zinc, red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose. Default: `zinc` |
| `size` | Badge size. Options: `sm`, `lg`. Default: medium |
| `variant` | Style variant. Options: `solid`, `pill` |
| `icon` | Icon name to display before text |
| `icon:trailing` | Icon name to display after text |
| `icon:variant` | Icon variant. Options: `outline`, `solid`, `mini`, `micro`. Default: `mini` |
| `as` | HTML element. Options: `button`. Default: `div` |
| `inset` | Negative margins for alignment. Options: `top`, `bottom`, `left`, `right`, or combinations |

### `<flux:badge.close>`

| Prop | Description |
|------|-------------|
| `icon` | Icon name. Default: `x-mark` |
| `icon:variant` | Icon variant. Options: `outline`, `solid`, `mini`, `micro`. Default: `mini` |

## Common Patterns

### Status Badge in Table

```blade
<flux:table.cell>
    <flux:badge color="green" size="sm">Paid</flux:badge>
</flux:table.cell>
```

### Category Tags

```blade
<flux:badge variant="pill" color="blue">Template</flux:badge>
<flux:badge variant="pill" color="green">SKU</flux:badge>
```

### Count Indicator

```blade
<flux:badge color="zinc" size="sm">{{ $count }}</flux:badge>
```

### Multi-Status Display

```blade
@if($order->status === 'PAID')
    <flux:badge color="green">Paid</flux:badge>
@elseif($order->status === 'PENDING')
    <flux:badge color="amber">Pending</flux:badge>
@elseif($order->status === 'REJECTED')
    <flux:badge color="red">Rejected</flux:badge>
@else
    <flux:badge color="zinc">{{ $order->status }}</flux:badge>
@endif
```

## ❌ Don't

- Don't use colors not in the official list (they won't render correctly)
- Don't use badges for actions (use buttons instead)
- Don't overuse `variant="solid"` (reduces visual hierarchy)
- Don't use large badges in compact table layouts

## ✅ Do

- Use consistent colors for same status across the app
- Prefer default variant for most status indicators
- Use `size="sm"` in tables and dense layouts
- Combine with icons for better recognition
- Use `variant="solid"` sparingly for critical statuses

**Reference**: [https://fluxui.dev/components/badge](https://fluxui.dev/components/badge)