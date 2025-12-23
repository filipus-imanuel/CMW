# Button Component (Flux Pro)

## Variants

```blade
<!-- Available variants (ONLY these are supported): -->
<flux:button variant="outline">Default/Outline</flux:button>  <!-- Default if not specified -->
<flux:button variant="primary">Primary Action</flux:button>   <!-- Main CTA -->
<flux:button variant="filled">Filled</flux:button>            <!-- Solid background -->
<flux:button variant="danger">Delete/Cancel</flux:button>     <!-- Red, destructive -->
<flux:button variant="ghost">Subtle Action</flux:button>      <!-- Transparent -->
<flux:button variant="subtle">Secondary</flux:button>         <!-- Light gray -->

<!-- ❌ WRONG: These variants do NOT exist -->
<flux:button variant="success">...</flux:button>   <!-- UnhandledMatchError! -->
<flux:button variant="warning">...</flux:button>   <!-- UnhandledMatchError! -->
```

## Colors

Use standard Tailwind color names with `color` prop (works with `primary` variant):

```blade
<flux:button variant="primary" color="zinc">Zinc</flux:button>
<flux:button variant="primary" color="red">Red</flux:button>
<flux:button variant="primary" color="green">Green</flux:button>
<flux:button variant="primary" color="blue">Blue</flux:button>
<!-- Available: zinc, red, orange, amber, yellow, lime, green, emerald, 
     teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose -->
```

## Usage Guidelines

- `primary` - Main action (Save, Submit, Confirm)
- `danger` - Destructive (Delete, Reject, Abort)
- `ghost` - Navigation (Back, Cancel in modal)
- `outline` - Secondary actions (Edit, View Details)
- `filled` - Alternative emphasis (less than primary)
- `subtle` - Minimal emphasis (auxiliary actions)

## Icons

```blade
<!-- Leading icon -->
<flux:button icon="arrow-down-tray">Export</flux:button>

<!-- Trailing icon -->
<flux:button icon:trailing="arrow-up-right">Open</flux:button>

<!-- Icon only (automatically square) -->
<flux:button icon="ellipsis-horizontal" />

<!-- Icon with variant -->
<flux:button icon="x-mark" icon:variant="outline" variant="subtle" />
```

## Sizes

```blade
<flux:button>Base</flux:button>              <!-- Default -->
<flux:button size="sm">Small</flux:button>
<flux:button size="xs">Extra small</flux:button>
```

## As Link

```blade
<!-- Renders as <a> tag -->
<flux:button href="{{ route('resource.index') }}" icon:trailing="arrow-up-right" wire:navigate>
    Visit Page
</flux:button>
```

## Loading State

```blade
<!-- Auto-loading with wire:click or type="submit" -->
<flux:button wire:click="save">Save changes</flux:button>

<!-- Disable auto-loading -->
<flux:button wire:click="save" :loading="false">Save</flux:button>
```

## Button Groups

```blade
<flux:button.group>
    <flux:button>Oldest</flux:button>
    <flux:button>Newest</flux:button>
    <flux:button>Top</flux:button>
</flux:button.group>

<!-- Icon group -->
<flux:button.group>
    <flux:button icon="bars-3-bottom-left" />
    <flux:button icon="bars-3" />
    <flux:button icon="bars-3-bottom-right" />
</flux:button.group>
```

## Additional Attributes

```blade
<!-- Full width -->
<flux:button variant="primary" class="w-full">Send invite</flux:button>

<!-- Square (automatic for icon-only) -->
<flux:button square>...</flux:button>

<!-- Form submission -->
<flux:button type="submit" variant="primary">Submit</flux:button>

<!-- Tooltip -->
<flux:button tooltip="Delete item" icon="trash" />

<!-- Inset (negative margins for alignment) -->
<flux:button variant="ghost" inset icon="x-mark" />
```

## Common Patterns

```blade
<!-- Navigation back button -->
<flux:button href="{{ route('resource.index') }}" icon="arrow-left" variant="ghost" wire:navigate>
    Back to Resources
</flux:button>

<!-- Primary action -->
<flux:button type="submit" variant="primary" icon="arrow-right-end-on-rectangle">
    Submit
</flux:button>

<!-- Delete action -->
<flux:button wire:click="confirmDelete({{ $id }})" variant="danger" icon="trash">
    Delete
</flux:button>

<!-- Action buttons aligned right -->
<div class="flex pt-2">
    <flux:spacer />
    <flux:button type="submit" variant="primary">Submit</flux:button>
</div>
```

## Reference Properties

| Property | Options | Default |
|----------|---------|---------|
| `variant` | `outline`, `primary`, `filled`, `danger`, `ghost`, `subtle` | `outline` |
| `size` | `base`, `sm`, `xs` | `base` |
| `icon` | Icon name (leading position) | - |
| `icon:trailing` | Icon name (trailing position) | - |
| `icon:variant` | `outline`, `solid`, `mini`, `micro` | `micro` |
| `color` | Tailwind color names | - |
| `href` | URL (renders as `<a>` tag) | - |
| `type` | `button`, `submit` | `button` |
| `loading` | `true`, `false` | `true` |
| `square` | Boolean | `false` |
| `inset` | Boolean or `top`, `bottom`, `left`, `right` | - |
| `tooltip` | String | - |

**Reference**: https://fluxui.dev/components/button
