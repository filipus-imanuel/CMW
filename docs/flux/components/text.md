# Flux Text Component

## Basic Usage
```blade
<flux:heading>Text component</flux:heading>
<flux:text class="mt-2">
    This is the standard text component for body copy and general content throughout your application.
</flux:text>
```

## Size Control
Use standard Tailwind classes to control text size:

```blade
<flux:text class="text-base">Base text size</flux:text>
<flux:text>Default text size</flux:text>
<flux:text class="text-xs">Smaller text</flux:text>
```

## Color Variants
```blade
<!-- Built-in variants -->
<flux:text variant="strong">Strong text color</flux:text>
<flux:text>Default text color</flux:text>
<flux:text variant="subtle">Subtle text color</flux:text>

<!-- Color prop -->
<flux:text color="blue">Colored text</flux:text>
```

## Links
```blade
<!-- Inline link within text -->
<flux:text>
    Visit our <flux:link href="#">documentation</flux:link> for more information.
</flux:text>

<!-- Link variants -->
<flux:link href="#">Default link</flux:link>
<flux:link href="#" variant="ghost">Ghost link</flux:link>
<flux:link href="#" variant="subtle">Subtle link</flux:link>

<!-- External link (opens in new tab) -->
<flux:link href="https://example.com" external>External site</flux:link>
```

## Component Reference

### `<flux:text>`
**Props:**
- `size` - Size of the text: `sm`, `default`, `lg`, `xl` (default: `default`)
- `variant` - Text variant: `strong`, `subtle` (default: `default`)
- `color` - Text color: `default`, `red`, `orange`, `yellow`, `lime`, `green`, `emerald`, `teal`, `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose`
- `inline` - If `true`, renders as `<span>` instead of `<p>`

### `<flux:link>`
**Props:**
- `href` - The URL that the link points to (required)
- `variant` - Link style: `default`, `ghost`, `subtle` (default: `default`)
- `external` - If `true`, opens in new tab

## Usage Guidelines
- Use `<flux:text>` for body copy and general content
- Combine with Tailwind classes for responsive sizing
- Use `variant="strong"` for emphasis, `variant="subtle"` for secondary text
- Prefer `color` prop over custom Tailwind text colors for consistency
- Use `inline` prop when nesting text within inline elements

**Reference:** https://fluxui.dev/components/text
