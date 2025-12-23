# Checkbox Component

Select one or multiple options from a set using Flux Pro checkbox components.

## Basic Usage

**Single checkbox with field wrapper:**
```blade
<flux:field variant="inline">
    <flux:checkbox wire:model="terms" />
    <flux:label>I agree to the terms and conditions</flux:label>
    <flux:error name="terms" />
</flux:field>
```

## Checkbox Group

**Vertical list of checkboxes:**
```blade
<flux:checkbox.group wire:model="notifications" label="Notifications">
    <flux:checkbox label="Push notifications" value="push" checked />
    <flux:checkbox label="Email" value="email" checked />
    <flux:checkbox label="In-app alerts" value="app" />
    <flux:checkbox label="SMS" value="sms" />
</flux:checkbox.group>
```

**With descriptions:**
```blade
<flux:checkbox.group wire:model="subscription" label="Subscription preferences">
    <flux:checkbox 
        checked
        value="newsletter"
        label="Newsletter"
        description="Receive our monthly newsletter with the latest updates and offers." />
    <flux:checkbox 
        value="updates"
        label="Product updates"
        description="Stay informed about new features and product updates." />
</flux:checkbox.group>
```

## Horizontal Layout

**Using fieldset for horizontal arrangement:**
```blade
<flux:fieldset>
    <flux:legend>Languages</flux:legend>
    <flux:description>Choose the languages you want to support.</flux:description>
    <div class="flex gap-4 *:gap-x-2">
        <flux:checkbox checked value="english" label="English" />
        <flux:checkbox checked value="spanish" label="Spanish" />
        <flux:checkbox value="french" label="French" />
        <flux:checkbox value="german" label="German" />
    </div>
</flux:fieldset>
```

## Check-All Pattern

**Control group with single checkbox:**
```blade
<flux:checkbox.group>
    <flux:checkbox.all />
    <flux:checkbox checked />
    <flux:checkbox />
    <flux:checkbox />
</flux:checkbox.group>
```

## States

**Checked by default:**
```blade
<flux:checkbox checked />
```

**Disabled:**
```blade
<flux:checkbox disabled />
```

**Indeterminate (partial selection):**
```blade
<flux:checkbox indeterminate />
```

## Pro Variants

### Checkbox Cards

**Horizontal cards with descriptions:**
```blade
<flux:checkbox.group wire:model="subscription" label="Subscription preferences" variant="cards" class="max-sm:flex-col">
    <flux:checkbox 
        checked
        value="newsletter"
        label="Newsletter"
        description="Get the latest updates and offers." />
    <flux:checkbox 
        value="updates"
        label="Product updates"
        description="Learn about new features and products." />
</flux:checkbox.group>
```

**Vertical cards:**
```blade
<flux:checkbox.group label="Subscription preferences" variant="cards" class="flex-col">
    <!-- checkboxes -->
</flux:checkbox.group>
```

**Cards with icons:**
```blade
<flux:checkbox.group label="Subscription preferences" variant="cards" class="flex-col">
    <flux:checkbox 
        checked
        value="newsletter"
        icon="newspaper"
        label="Newsletter"
        description="Get the latest updates and offers." />
    <flux:checkbox 
        value="updates"
        icon="cube"
        label="Product updates"
        description="Learn about new features and products." />
</flux:checkbox.group>
```

**Custom card content (using slot):**
```blade
<flux:checkbox.group label="Subscription preferences" variant="cards" class="flex-col">
    <flux:checkbox checked value="newsletter">
        <flux:checkbox.indicator />
        <div class="flex-1">
            <flux:heading class="leading-4">Newsletter</flux:heading>
            <flux:text size="sm" class="mt-2">Get the latest updates and offers.</flux:text>
        </div>
    </flux:checkbox>
</flux:checkbox.group>
```

### Pills

**Tag-like checkboxes for filters/categories:**
```blade
<flux:checkbox.group wire:model="categories" label="Categories" variant="pills">
    <flux:checkbox value="fantasy" label="Fantasy" />
    <flux:checkbox value="science-fiction" label="Science fiction" />
    <flux:checkbox value="horror" label="Horror" />
    <flux:checkbox value="mystery" label="Mystery" />
</flux:checkbox.group>
```

### Buttons

**Toolbar-style checkboxes:**
```blade
<flux:checkbox.group wire:model="features" label="Features" variant="buttons">
    <flux:checkbox value="notifications" icon="bell" label="Notifications" />
    <flux:checkbox value="analytics" icon="chart-bar" label="Analytics" />
    <flux:checkbox value="backups" icon="cloud-arrow-up" label="Backups" />
</flux:checkbox.group>
```

## Component Reference

### `<flux:checkbox>`

**Attributes:**
- `wire:model` - Binds to Livewire property
- `label` - Label text next to checkbox
- `description` - Help text below checkbox
- `value` - Value when used in group (included in array when checked)
- `checked` - Default checked state
- `indeterminate` - Dash instead of checkmark (partial selection)
- `disabled` - Prevents interaction
- `invalid` - Error styling

**Data attributes:**
- `data-flux-checkbox` - Root element identifier
- `data-checked` - Applied when checked
- `data-indeterminate` - Applied when indeterminate

### `<flux:checkbox.group>`

**Attributes:**
- `wire:model` - Binds to array of selected values
- `label` - Label above group (wraps in flux:field)
- `description` - Help text below label
- `variant` - Visual style: `default`, `cards` (Pro), `pills` (Pro), `buttons` (Pro)
- `disabled` - Disables all checkboxes
- `invalid` - Error styling for all

**Slots:**
- `default` - Checkboxes and other elements

### `<flux:checkbox.all>`

Controls all checkboxes in group - checked when all checked, unchecked when none, indeterminate when partial.

**Attributes:**
- `label` - Label text
- `description` - Help text
- `disabled` - Prevents interaction

## Usage Guidelines

- Use `wire:model` on group OR individual checkboxes (not both)
- Cards/pills/buttons variants require Flux Pro
- Use `flex-col` class for vertical card layouts
- `flux:checkbox.all` for "select all" functionality
- Use `indeterminate` for partial selection states

**Reference:** https://fluxui.dev/components/checkbox
