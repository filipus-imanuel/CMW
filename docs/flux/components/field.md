# Field Component

Encapsulate input elements with labels, descriptions, and validation messages.

## Basic Usage

```blade
<flux:field>
    <flux:label>Email</flux:label>
    <flux:input wire:model="email" type="email" />
    <flux:error name="email" />
</flux:field>
```

## Shorthand Props

All form controls support `label` and `description` props directly (wraps in `flux:field` automatically):

```blade
<flux:input wire:model="email" label="Email" type="email" />
```

## With Description

Description appears between label and input:

```blade
<flux:field>
    <flux:label>Password</flux:label>
    <flux:description>
        Must be at least 8 characters long, include an uppercase letter, a number, and a special character.
    </flux:description>
    <flux:input type="password" />
    <flux:error name="password" />
</flux:field>

<!-- Shorthand -->
<flux:input 
    type="password" 
    label="Password" 
    description="Must be at least 8 characters long, include an uppercase letter, a number, and a special character." 
/>
```

## With Trailing Description

Position description below input:

```blade
<flux:field>
    <flux:label>Password</flux:label>
    <flux:input type="password" />
    <flux:error name="password" />
    <flux:description>
        Must be at least 8 characters long, include an uppercase letter, a number, and a special character.
    </flux:description>
</flux:field>

<!-- Shorthand -->
<flux:input 
    type="password" 
    label="Password" 
    description:trailing="Must be at least 8 characters long, include an uppercase letter, a number, and a special character." 
/>
```

## With Badge

Add badges like "Required" or "Optional":

```blade
<flux:field>
    <flux:label badge="Required">Email</flux:label>
    <flux:input type="email" required />
    <flux:error name="email" />
</flux:field>

<flux:field>
    <flux:label badge="Optional">Phone number</flux:label>
    <flux:input type="phone" placeholder="(555) 555-5555" mask="(999) 999-9999" />
    <flux:error name="phone" />
</flux:field>
```

## Split Layout

Display multiple fields horizontally:

```blade
<div class="grid grid-cols-2 gap-4">
    <flux:input label="First name" placeholder="River" />
    <flux:input label="Last name" placeholder="Porzio" />
</div>
```

## Fieldset

Group related fields with heading:

```blade
<flux:fieldset>
    <flux:legend>Shipping address</flux:legend>
    
    <div class="space-y-6">
        <flux:input label="Street address line 1" placeholder="123 Main St" class="max-w-sm" />
        <flux:input label="Street address line 2" placeholder="Apartment, studio, or floor" class="max-w-sm" />
        
        <div class="grid grid-cols-2 gap-x-4 gap-y-6">
            <flux:input label="City" placeholder="San Francisco" />
            <flux:input label="State / Province" placeholder="CA" />
            <flux:input label="Postal / Zip code" placeholder="12345" />
            <flux:select label="Country">
                <option selected>United States</option>
                <!-- ... -->
            </flux:select>
        </div>
    </div>
</flux:fieldset>
```

## Properties

### flux:field

Container for form controls with labels, descriptions, and errors.

| Property | Description |
|----------|-------------|
| `variant` | Visual style: `block` (default), `inline` |

### flux:label

| Property | Description |
|----------|-------------|
| `badge` | Badge text (e.g., "Required", "Optional") |

**Slots:**
- `default` - Label text content
- `trailing` - Text at end of label

### flux:description

Help text for the field. No props.

**Slots:**
- `default` - Description text content

### flux:error

Displays validation error messages.

| Property | Description |
|----------|-------------|
| `name` | Field name to display errors for |
| `message` | Custom error message (optional) |
| `bag` | Error bag name (default: `default`) |

**Slots:**
- `default` - Custom error message content

### flux:fieldset

Groups related form fields.

| Property | Description |
|----------|-------------|
| `legend` | Fieldset heading text |
| `description` | Optional description for fieldset |

**Slots:**
- `default` - Grouped form fields

### flux:legend

Fieldset heading. No props.

**Slots:**
- `default` - Heading text

## Guidelines

- Use shorthand props (`label`, `description`) for cleaner syntax
- Badges help indicate required/optional fields
- Use `description:trailing` to position help text below input
- `flux:fieldset` groups logically related fields
- `flux:error` automatically displays validation errors from Livewire
- All Flux form controls support `label` and `description` props
- Use grid layouts for horizontal field arrangements

**Reference**: https://fluxui.dev/components/field
