# Flux Separator Component

Visually divide sections of content or groups of items.

## Basic Usage

Horizontal separator (default):

```blade
<flux:separator />
```

## With Text

Add descriptive text to the separator:

```blade
<flux:separator text="or" />
```

## Vertical Separator

Separate contents horizontally stacked:

```blade
<flux:button>Log in</flux:button>
<flux:separator vertical />
<flux:button>Sign up</flux:button>
```

## Limited Height

Control vertical separator height with margin classes:

```blade
<flux:button>Log in</flux:button>
<flux:separator vertical class="my-2" />
<flux:button>Sign up</flux:button>
```

## Subtle Variant

Separator that blends into the background:

```blade
<flux:separator vertical variant="subtle" />
```

## API Reference

### `<flux:separator>`

**Props:**
- `vertical` - Display vertical separator (boolean, default: horizontal)
- `variant` - Visual style: `subtle` or default standard separator
- `text` - Optional text displayed in center of separator (string)
- `orientation` - Alternative to `vertical` prop: `horizontal` (default), `vertical`

**Common Classes:**
- `my-*` - Shorten vertical separators (e.g., `class="my-2"`)

**Data Attributes:**
- `data-flux-separator` - Root element identifier

## Guidelines

- **Horizontal usage**: Default for separating vertically stacked content sections
- **Vertical usage**: For horizontally arranged items (toolbars, button groups)
- **With text**: Use for login forms ("or" between methods), section labels
- **Height control**: Use margin classes (`my-2`, `my-4`) to limit vertical separator height
- **Subtle variant**: Use when you need less visual emphasis, blends better with backgrounds
- **Common patterns**:
  - Form sections: Horizontal separator between groups
  - Auth forms: Separator with "or" text between OAuth and email login
  - Toolbars: Vertical separators between button groups
  - Menus: Horizontal separators between option groups

**Reference:** https://fluxui.dev/components/separator
