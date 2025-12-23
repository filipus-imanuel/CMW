# Flux Radio Component

Select one option from a set of mutually exclusive choices. Perfect for single-choice questions and settings.

## Basic Usage

```blade
<flux:radio.group wire:model="payment" label="Select your payment method">
    <flux:radio value="cc" label="Credit Card" checked />
    <flux:radio value="paypal" label="Paypal" />
    <flux:radio value="ach" label="Bank transfer" />
</flux:radio.group>
```

## With Descriptions

```blade
<flux:radio.group label="Role">
    <flux:radio
        name="role"
        value="administrator"
        label="Administrator"
        description="Administrator users can perform any action."
        checked
    />
    <flux:radio
        name="role"
        value="editor"
        label="Editor"
        description="Editor users have the ability to read, create, and update."
    />
    <flux:radio
        name="role"
        value="viewer"
        label="Viewer"
        description="Viewer users only have the ability to read. Create, and update are restricted."
    />
</flux:radio.group>
```

## Within Fieldset

Group radio inputs inside a fieldset for better semantic structure:

```blade
<flux:fieldset>
    <flux:legend>Role</flux:legend>
    
    <flux:radio.group>
        <flux:radio
            value="administrator"
            label="Administrator"
            description="Administrator users can perform any action."
            checked
        />
        <flux:radio
            value="editor"
            label="Editor"
            description="Editor users have the ability to read, create, and update."
        />
        <flux:radio
            value="viewer"
            label="Viewer"
            description="Viewer users only have the ability to read. Create, and update are restricted."
        />
    </flux:radio.group>
</flux:fieldset>
```

## Segmented Variant

A more compact alternative to standard radio buttons:

```blade
<!-- Default size -->
<flux:radio.group wire:model="role" label="Role" variant="segmented">
    <flux:radio label="Admin" />
    <flux:radio label="Editor" />
    <flux:radio label="Viewer" />
</flux:radio.group>

<!-- Small size -->
<flux:radio.group wire:model="role" label="Role" variant="segmented" size="sm">
    <flux:radio label="Admin" />
    <flux:radio label="Editor" />
    <flux:radio label="Viewer" />
</flux:radio.group>

<!-- With icons -->
<flux:radio.group wire:model="role" variant="segmented">
    <flux:radio label="Admin" icon="wrench" />
    <flux:radio label="Editor" icon="pencil-square" />
    <flux:radio label="Viewer" icon="eye" />
</flux:radio.group>
```

## Cards Variant (Pro)

A bordered alternative to standard radio buttons:

```blade
<!-- Horizontal cards (responsive) -->
<flux:radio.group wire:model="shipping" label="Shipping" variant="cards" class="max-sm:flex-col">
    <flux:radio value="standard" label="Standard" description="4-10 business days" checked />
    <flux:radio value="fast" label="Fast" description="2-5 business days" />
    <flux:radio value="next-day" label="Next day" description="1 business day" />
</flux:radio.group>

<!-- Vertical cards -->
<flux:radio.group label="Shipping" variant="cards" class="flex-col">
    <flux:radio value="standard" label="Standard" description="4-10 business days" />
    <flux:radio value="fast" label="Fast" description="2-5 business days" />
    <flux:radio value="next-day" label="Next day" description="1 business day" />
</flux:radio.group>

<!-- With icons -->
<flux:radio.group label="Shipping" variant="cards" class="max-sm:flex-col">
    <flux:radio value="standard" icon="truck" label="Standard" description="4-10 business days" />
    <flux:radio value="fast" icon="cube" label="Fast" description="2-5 business days" />
    <flux:radio value="next-day" icon="clock" label="Next day" description="1 business day" />
</flux:radio.group>

<!-- Without indicators -->
<flux:radio.group label="Shipping" variant="cards" :indicator="false" class="max-sm:flex-col">
    <flux:radio value="standard" icon="truck" label="Standard" description="4-10 business days" />
    <flux:radio value="fast" icon="cube" label="Fast" description="2-5 business days" />
    <flux:radio value="next-day" icon="clock" label="Next day" description="1 business day" />
</flux:radio.group>
```

## Custom Card Content (Pro)

Compose custom cards through the radio component slot:

```blade
<flux:radio.group label="Shipping" variant="cards" class="max-sm:flex-col">
    <flux:radio value="standard" checked>
        <flux:radio.indicator />
        <div class="flex-1">
            <flux:heading class="leading-4">Standard</flux:heading>
            <flux:text size="sm" class="mt-2">4-10 business days</flux:text>
        </div>
    </flux:radio>
    
    <flux:radio value="fast">
        <flux:radio.indicator />
        <div class="flex-1">
            <flux:heading class="leading-4">Fast</flux:heading>
            <flux:text size="sm" class="mt-2">2-5 business days</flux:text>
        </div>
    </flux:radio>
    
    <flux:radio value="next-day">
        <flux:radio.indicator />
        <div class="flex-1">
            <flux:heading class="leading-4">Next day</flux:heading>
            <flux:text size="sm" class="mt-2">1 business day</flux:text>
        </div>
    </flux:radio>
</flux:radio.group>
```

## Pills Variant (Pro)

Compact, rounded radio buttons that look like tags or badges:

```blade
<flux:radio.group wire:model="priority" label="Priority" variant="pills">
    <flux:radio value="low" label="Low" />
    <flux:radio value="medium" label="Medium" />
    <flux:radio value="high" label="High" />
    <flux:radio value="critical" label="Critical" />
</flux:radio.group>
```

## Buttons Variant (Pro)

Button-style radio options that look like a toolbar:

```blade
<flux:radio.group variant="buttons" class="w-full *:flex-1" label="Feedback type">
    <flux:radio icon="bug-ant" checked>Bug report</flux:radio>
    <flux:radio icon="light-bulb">Suggestion</flux:radio>
    <flux:radio icon="question-mark-circle">Question</flux:radio>
</flux:radio.group>
```

## API Reference

### `<flux:radio.group>`

Container for grouping radio buttons together.

**Props:**
- `wire:model` - Bind selection to Livewire property (string)
- `label` - Label text above the group (auto-wraps in `flux:field`)
- `description` - Help text below label, above radios (when `label` provided)
- `variant` - Visual style: `default`, `segmented`, `cards`, `pills`, `buttons`
- `size` - Size for segmented variant: `sm`, default
- `invalid` - Apply error styling (boolean)

**Slots:**
- `default` - Radio buttons to be grouped

**Data Attributes:**
- `data-flux-radio-group` - Root element identifier

### `<flux:radio>`

Individual radio button option.

**Props:**
- `label` - Label text (auto-wraps in `flux:field` when provided)
- `description` - Help text below label
- `value` - Value when used in a group (string)
- `checked` - Selected by default (boolean)
- `disabled` - Prevent user interaction (boolean)
- `icon` - Icon name for segmented/buttons variants (string)

**Slots:**
- `default` - Custom content for card variant

**Data Attributes:**
- `data-flux-radio` - Root element identifier
- `data-checked` - Present when selected

### `<flux:radio.indicator>`

Indicator component for custom card layouts. Use inside `<flux:radio>` slot when creating custom card content.

## Guidelines

- **Mutually exclusive**: Only one radio can be selected per group
- **Always use groups**: Wrap radios in `flux:radio.group` for proper behavior
- **Responsive cards**: Use `class="max-sm:flex-col"` for mobile-friendly card layouts
- **Equal width buttons**: Use `class="w-full *:flex-1"` for full-width button groups
- **Variants**: Choose based on context:
  - `default` - Standard forms
  - `segmented` - Compact selections (filters, views)
  - `cards` - Visually prominent options (pricing, plans)
  - `pills` - Tags/categories (filters, priorities)
  - `buttons` - Actions/toolbars (feedback types, modes)
- **Descriptions**: Use for clarity when options need explanation
- **Icons**: Add visual cues for faster recognition
- **Default selection**: Always set `checked` on one option for better UX

**Reference:** https://fluxui.dev/components/radio
