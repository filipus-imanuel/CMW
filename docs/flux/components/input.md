# Input Component

Capture user data with various forms of text input.

## Basic Usage

```blade
<flux:field>
    <flux:label>Username</flux:label>
    <flux:description>This will be publicly displayed.</flux:description>
    <flux:input />
    <flux:error name="username" />
</flux:field>
```

## Shorthand

Pass `label` and `description` directly (auto-wraps in `flux:field`):

```blade
<flux:input label="Username" description="This will be publicly displayed." />
```

## Class Targeting

Input has wrapper `div` and inner `input` element. Target them separately:

```blade
<!-- Apply to wrapper -->
<flux:input class="max-w-xs" />

<!-- Apply directly to input element -->
<flux:input class:input="font-mono" />

<!-- Both -->
<flux:input class="max-w-xs" class:input="font-mono" />
```

## Input Types

```blade
<!-- Email -->
<flux:input type="email" label="Email" />

<!-- Password -->
<flux:input type="password" label="Password" />

<!-- Date -->
<flux:input type="date" max="2999-12-31" label="Date" />

<!-- File (single) -->
<flux:input type="file" wire:model="logo" label="Logo" />

<!-- File (multiple) -->
<flux:input type="file" wire:model="attachments" label="Attachments" multiple />
```

## Sizes

```blade
<!-- Small -->
<flux:input size="sm" placeholder="Filter by..." />

<!-- Extra small -->
<flux:input size="xs" placeholder="Compact..." />
```

## States

```blade
<!-- Disabled -->
<flux:input disabled label="Email" />

<!-- Readonly -->
<flux:input readonly variant="filled" value="BA7K7QZ511S8Z2K" />

<!-- Invalid -->
<flux:input invalid />
```

## Input Masking

Use [Alpine's mask plugin](https://alpinejs.dev/plugins/mask):

```blade
<!-- Phone mask -->
<flux:input mask="(999) 999-9999" value="7161234567" />

<!-- Dynamic mask -->
<flux:input mask:dynamic="$money($input)" value="1234.56" />
```

## Icons

```blade
<!-- Leading icon -->
<flux:input icon="magnifying-glass" placeholder="Search orders" />

<!-- Trailing icon -->
<flux:input icon:trailing="credit-card" placeholder="4444-4444-4444-4444" />

<!-- Loading icon -->
<flux:input icon:trailing="loading" placeholder="Search transactions" />
```

## Icon Buttons

Custom buttons inside input:

```blade
<!-- Clear button -->
<flux:input placeholder="Search orders">
    <x-slot name="iconTrailing">
        <flux:button size="sm" variant="subtle" icon="x-mark" class="-mr-1" />
    </x-slot>
</flux:input>

<!-- Toggle password visibility -->
<flux:input type="password" value="password">
    <x-slot name="iconTrailing">
        <flux:button size="sm" variant="subtle" icon="eye" class="-mr-1" />
    </x-slot>
</flux:input>
```

## Special Features

```blade
<!-- Clearable (shows clear button when has content) -->
<flux:input placeholder="Search orders" clearable />

<!-- Viewable (toggle password visibility) -->
<flux:input type="password" value="password" viewable />

<!-- Copyable (copy button - HTTPS only) -->
<flux:input icon="key" value="FLUX-1234-5678-ABCD-EFGH" readonly copyable />
```

## Keyboard Hint

```blade
<flux:input kbd="⌘K" icon="magnifying-glass" placeholder="Search..." />
```

## As Button

Render input as button element:

```blade
<flux:input as="button" placeholder="Search..." icon="magnifying-glass" kbd="⌘K" />
```

## Input Groups

### With Buttons

```blade
<flux:input.group>
    <flux:input placeholder="Post title" />
    <flux:button icon="plus">New post</flux:button>
</flux:input.group>

<flux:input.group>
    <flux:select class="max-w-fit">
        <flux:select.option selected>USD</flux:select.option>
        <!-- ... -->
    </flux:select>
    <flux:input placeholder="$99.99" />
</flux:input.group>
```

### With Text Prefix/Suffix

```blade
<!-- Prefix -->
<flux:input.group>
    <flux:input.group.prefix>https://</flux:input.group.prefix>
    <flux:input placeholder="example.com" />
</flux:input.group>

<!-- Suffix -->
<flux:input.group>
    <flux:input placeholder="chunky-spaceship" />
    <flux:input.group.suffix>.brand.com</flux:input.group.suffix>
</flux:input.group>
```

### With Field Label

```blade
<flux:field>
    <flux:label>Website</flux:label>
    <flux:input.group>
        <flux:input.group.prefix>https://</flux:input.group.prefix>
        <flux:input wire:model="website" placeholder="example.com" />
    </flux:input.group>
    <flux:error name="website" />
</flux:field>
```

## Properties

### flux:input

| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property |
| `label` | Label text (wraps in `flux:field`) |
| `description` | Help text above input |
| `description:trailing` | Help text below input |
| `placeholder` | Placeholder text |
| `size` | Size: `sm`, `xs` |
| `variant` | Style: `filled`, default is `outline` |
| `disabled` | Prevent interaction |
| `readonly` | Read-only state |
| `invalid` | Error styling |
| `multiple` | Allow multiple files (file inputs) |
| `mask` | Input mask pattern (e.g., `99/99/9999`) |
| `mask:dynamic` | Dynamic mask (e.g., `$money($input)`) |
| `icon` | Leading icon name |
| `icon:trailing` | Trailing icon name |
| `kbd` | Keyboard shortcut hint |
| `clearable` | Show clear button when has content |
| `copyable` | Show copy button (HTTPS only) |
| `viewable` | Toggle password visibility (password inputs) |
| `as` | Render as: `button`, default is `input` |
| `class:input` | Classes for input element (not wrapper) |

**Slots:**
- `icon` / `icon:leading` - Custom leading content
- `icon:trailing` - Custom trailing content (e.g., buttons)

### flux:input.group

Container for grouped inputs with prefix/suffix.

**Slots:**
- `default` - Input group content

### flux:input.group.prefix

Content before input.

**Slots:**
- `default` - Prefix content (text, icons, buttons)

### flux:input.group.suffix

Content after input.

**Slots:**
- `default` - Suffix content (text, icons, buttons)

## Guidelines

- Use shorthand props (`label`, `description`) for cleaner syntax
- Use `class:input` to style inner input element directly
- Input masking requires Alpine's mask plugin
- Use `clearable`, `viewable`, `copyable` for common button patterns
- `copyable` only works on HTTPS
- Use `flux:input.group` to combine inputs with buttons/text
- File inputs support `multiple` attribute
- Date inputs support `max`/`min` attributes

**Reference**: https://fluxui.dev/components/input
