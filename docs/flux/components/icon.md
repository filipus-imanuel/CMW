# Icon Component

Flux uses Heroicons for its icon collection. Search for icons at [Heroicons](https://heroicons.com/).

## Basic Usage

```blade
<flux:icon.bolt />
```

## Variants

Four variants available for each icon:

```blade
<!-- Outline (default) - 24px, outline -->
<flux:icon.bolt />

<!-- Solid - 24px, filled -->
<flux:icon.bolt variant="solid" />

<!-- Mini - 20px, filled -->
<flux:icon.bolt variant="mini" />

<!-- Micro - 16px, filled -->
<flux:icon.bolt variant="micro" />
```

**Default sizes by variant:**
| Variant | Size |
|---------|------|
| `outline` | 24x24 pixels (default) |
| `solid` | 24x24 pixels |
| `mini` | 20x20 pixels |
| `micro` | 16x16 pixels |

## Custom Sizes

Control size using Tailwind's `size-*` utility:

```blade
<flux:icon.bolt class="size-12" />
<flux:icon.bolt class="size-10" />
<flux:icon.bolt class="size-8" />
```

**Note**: Avoid tweaking icon sizes. Each variant is designed for its default size.

## Color

Customize color using Tailwind's text color utilities:

```blade
<flux:icon.bolt variant="solid" class="text-amber-500 dark:text-amber-300" />
```

## Loading Spinner

Special loading icon (not part of Heroicons):

```blade
<flux:icon.loading />
```

## Dynamic Icons

Set icon dynamically using the `name` prop:

```blade
<flux:icon name="bolt" />
```

## Lucide Icons

Import additional icons from [Lucide](https://lucide.dev/) using artisan command:

```bash
# Interactive selection
php artisan flux:icon

# Specify icons directly
php artisan flux:icon crown grip-vertical github
```

**Usage:**
```blade
<flux:icon.crown />
<flux:icon.grip-vertical />
<flux:icon.github />
```

## Custom Icons

Create custom icons in `resources/views/flux/icon/`:

```
- resources
    - views
        - flux
            - icon
                - wink.blade.php
```

**Template structure:**

```blade
@php $attributes = $unescapedForwardedAttributes ?? $attributes; @endphp

@props([
    'variant' => 'outline',
])

@php
$classes = Flux::classes('shrink-0')
    ->add(match($variant) {
        'outline' => '[:where(&)]:size-6',
        'solid' => '[:where(&)]:size-6',
        'mini' => '[:where(&)]:size-5',
        'micro' => '[:where(&)]:size-4',
    });
@endphp

{{-- Your SVG code here: --}}
<svg {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true" ...>
    <!-- SVG paths -->
</svg>
```

**Usage:**
```blade
<flux:icon.wink />
```

## Properties

### flux:icon.*

All icon components share the same props.

| Property | Description |
|----------|-------------|
| `variant` | Style: `outline` (default), `solid`, `mini`, `micro` |

**Tailwind Classes:**
- `size-*` - Control icon size (e.g., `size-8`, `size-12`)
- `text-*` - Control icon color (e.g., `text-blue-500`)

**Data Attributes:**
- `data-flux-icon` - Applied to root SVG element

## Guidelines

- Default variant is `outline` (24x24px)
- Use `mini` (20px) for compact spaces
- Use `micro` (16px) for tight layouts
- Avoid custom sizes—use default variant sizes
- Color icons with Tailwind's `text-*` utilities
- Use `flux:icon.loading` for loading states
- Use dynamic icons when icon name is variable
- Import Lucide icons for broader icon set
- Custom icons should follow template structure for compatibility

**Reference**: https://fluxui.dev/components/icon
