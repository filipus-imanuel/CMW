# Callout

Highlight important information or guide users toward key actions.

## Basic Usage

```blade
<flux:callout icon="clock">
    <flux:callout.heading>Upcoming maintenance</flux:callout.heading>
    <flux:callout.text>
        Our servers will be undergoing scheduled maintenance this Sunday from 2 AM - 5 AM UTC.
        <flux:callout.link href="#">Learn more</flux:callout.link>
    </flux:callout.text>
</flux:callout>
```

## Shorthand Syntax

```blade
<flux:callout 
    icon="information-circle" 
    heading="Your account has been created" 
    text="Welcome to the platform!" 
/>
```

## Variants

Predefined variants convey specific tone or urgency:

```blade
<flux:callout variant="secondary" icon="information-circle" heading="Info message" />
<flux:callout variant="success" icon="check-circle" heading="Success message" />
<flux:callout variant="warning" icon="exclamation-circle" heading="Warning message" />
<flux:callout variant="danger" icon="x-circle" heading="Error message" />
```

## Colors

Available colors: `zinc`, `red`, `orange`, `amber`, `yellow`, `lime`, `green`, `emerald`, `teal`, `cyan`, `sky`, `blue`, `indigo`, `violet`, `purple`, `fuchsia`, `pink`, `rose`

```blade
<flux:callout color="amber" icon="exclamation-triangle" heading="Caution" />
<flux:callout color="blue" icon="information-circle" heading="Information" />
<flux:callout color="purple" icon="sparkles" heading="New Feature" />
```

## With Actions

```blade
<flux:callout icon="clock">
    <flux:callout.heading>Subscription expiring soon</flux:callout.heading>
    <flux:callout.text>Your plan expires in 3 days.</flux:callout.text>
    <x-slot name="actions">
        <flux:button>Renew now</flux:button>
        <flux:button variant="ghost">View plans</flux:button>
    </x-slot>
</flux:callout>
```

## Inline Actions

Use `inline` prop to display actions inline with callout:

```blade
<flux:callout icon="cube" variant="secondary" inline>
    <flux:callout.heading>Your package is delayed</flux:callout.heading>
    <x-slot name="actions">
        <flux:button>Track order</flux:button>
        <flux:button variant="ghost">Reschedule</flux:button>
    </x-slot>
</flux:callout>
```

## Dismissible

Add close button using `controls` slot:

```blade
<div x-data="{ visible: true }" x-show="visible" x-collapse>
    <flux:callout icon="bell" variant="secondary">
        <flux:callout.heading>Notification</flux:callout.heading>
        <flux:callout.text>Your update is ready.</flux:callout.text>
        <x-slot name="controls">
            <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
        </x-slot>
    </flux:callout>
</div>
```

## Icon Inside Heading

```blade
<flux:callout>
    <flux:callout.heading icon="newspaper">Policy update</flux:callout.heading>
    <flux:callout.text>We've updated our Terms of Service.</flux:callout.text>
</flux:callout>
```

## Custom Icon

```blade
<flux:callout>
    <x-slot name="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <!-- Custom SVG path -->
        </svg>
    </x-slot>
    <flux:callout.heading>Custom heading</flux:callout.heading>
    <flux:callout.text>Your content here.</flux:callout.text>
</flux:callout>
```

## Props Reference

### `<flux:callout>`
- `icon` - Icon name (e.g., `clock`, `exclamation-triangle`)
- `icon:variant` - Icon variant (e.g., `outline`)
- `variant` - Options: `secondary`, `success`, `warning`, `danger` (default: `secondary`)
- `color` - Custom color (e.g., `red`, `blue`, `purple`)
- `inline` - Display actions inline (default: `false`)
- `heading` - Shorthand for `<flux:callout.heading>`
- `text` - Shorthand for `<flux:callout.text>`

### Slots
- `icon` - Custom icon element
- `actions` - Buttons or links
- `controls` - UI elements (e.g., close button)

### `<flux:callout.heading>`
- `icon` - Icon inside heading
- `icon:variant` - Icon variant

### `<flux:callout.link>`
- `href` - Link URL
- `external` - Open in new tab (default: `false`)

**Reference**: https://fluxui.dev/components/callout
