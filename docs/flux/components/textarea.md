# Flux Textarea Component

## Basic Usage
```blade
<flux:textarea />
```

## With Label and Binding
```blade
<flux:textarea wire:model="notes" label="Order notes" />
```

## With Placeholder
```blade
<flux:textarea 
    label="Order notes" 
    placeholder="No lettuce, tomato, or onion..." 
/>
```

## Fixed Row Height
```blade
<!-- Default: 4 rows -->
<flux:textarea label="Note" />

<!-- Custom row height -->
<flux:textarea rows="2" label="Note" />
<flux:textarea rows="6" label="Description" />
```

## Auto-sizing Textarea
Uses CSS `field-sizing` property to automatically adjust height based on content:

```blade
<flux:textarea rows="auto" />
```

**Note:** Not supported in all browsers. Check [caniuse.com](https://caniuse.com/?search=field-sizing) for browser compatibility.

## Resize Control
```blade
<!-- Vertical resize only (default) -->
<flux:textarea resize="vertical" />

<!-- No resize -->
<flux:textarea resize="none" />

<!-- Horizontal resize only -->
<flux:textarea resize="horizontal" />

<!-- Both directions -->
<flux:textarea resize="both" />
```

## With Description
```blade
<!-- Description above textarea -->
<flux:textarea 
    label="Comments" 
    description="Provide detailed feedback" 
/>

<!-- Description below textarea -->
<flux:textarea 
    label="Comments" 
    description:trailing="Max 500 characters" 
/>
```

## With Badge
```blade
<flux:textarea label="Notes" badge="Required" />
```

## Error State
```blade
<flux:textarea label="Comments" invalid />
```

## Component Reference

### `<flux:textarea>`
**Props:**
- `wire:model` - Binds textarea to Livewire property
- `label` - Label text displayed above textarea (auto-wraps in `flux:field`)
- `placeholder` - Placeholder text when empty
- `description` - Help text displayed between label and textarea
- `description:trailing` - Help text displayed below textarea instead of above
- `badge` - Badge text displayed at end of label
- `rows` - Number of visible text lines (default: `4`, use `"auto"` for auto-sizing)
- `resize` - Resize behavior: `vertical` (default), `horizontal`, `both`, `none`
- `invalid` - If `true`, applies error styling

**Attributes:**
- `data-flux-textarea` - Applied for styling and identification

## Usage Guidelines
- Default height is 4 rows, adjust with `rows` prop as needed
- Use `rows="auto"` for dynamic content that varies in length
- Set `resize="none"` to prevent user resizing (useful for fixed layouts)
- Combine `label` and `badge` props for required field indicators
- Use `description` for helper text, `description:trailing` for character limits
- When using `label` prop, component automatically wraps in `flux:field`

**Reference:** https://fluxui.dev/components/textarea
