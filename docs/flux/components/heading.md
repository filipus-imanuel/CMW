# Heading Component

A consistent heading component for your application.

## Basic Usage

```blade
<flux:heading>User profile</flux:heading>
<flux:text class="mt-2">
    This information will be displayed publicly.
</flux:text>
```

## Sizes

Three heading sizes available:

```blade
<!-- Default (base) - 14px -->
<flux:heading>Default</flux:heading>

<!-- Large - 16px -->
<flux:heading size="lg">Large</flux:heading>

<!-- Extra large - 24px -->
<flux:heading size="xl">Extra large</flux:heading>
```

**Usage guidelines:**
- **Base (14px)**: Use liberally—input labels, toast labels
- **Large (16px)**: Use sparingly—modal headings, card headings
- **Extra large (24px)**: Use rarely—hero text

## Heading Level

Control HTML heading level (`<h1>`, `<h2>`, `<h3>`, `<h4>`):

```blade
<flux:heading level="3">User profile</flux:heading>
<flux:text class="mt-2">
    This information will be displayed publicly.
</flux:text>
```

**Note**: Without `level` prop, renders as `<div>` element.

## Examples

### Leading Subheading

Place subheading above heading:

```blade
<div>
    <flux:text>Year to date</flux:text>
    <flux:heading size="xl" class="mb-1">$7,532.16</flux:heading>
    <div class="flex items-center gap-2">
        <flux:icon.arrow-trending-up variant="micro" class="text-green-600 dark:text-green-500" />
        <span class="text-sm text-green-600 dark:text-green-500">15.2%</span>
    </div>
</div>
```

## Properties

### flux:heading

| Property | Type | Description |
|----------|------|-------------|
| `size` | string | Size: `base` (default), `lg`, `xl` |
| `level` | number | HTML heading level: `1`, `2`, `3`, `4` (default: renders as `div`) |
| `accent` | bool | Apply accent color styling |

### flux:text

Companion component for body text.

| Property | Type | Description |
|----------|------|-------------|
| `size` | string | Size: `sm`, `base` (default), `lg`, `xl` |

## Guidelines

- Use semantic heading levels (`level` prop) for accessibility
- Default size (`base`) should be your most common heading
- Reserve `xl` size for hero sections and major page titles
- Pair with `flux:text` for descriptions
- Without `level` prop, heading renders as `<div>` (no semantic meaning)
- Use `accent` prop for emphasis on key headings

**Reference**: https://fluxui.dev/components/heading
