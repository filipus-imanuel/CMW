# Flux Accordion Component

## Overview
Collapse and expand sections of content. Perfect for FAQs and content-heavy areas.

---

## Basic Usage

```blade
<flux:accordion>
    <flux:accordion.item>
        <flux:accordion.heading>What's your refund policy?</flux:accordion.heading>
        <flux:accordion.content>
            If you are not satisfied with your purchase, we offer a 30-day money-back guarantee.
        </flux:accordion.content>
    </flux:accordion.item>
    
    <flux:accordion.item>
        <flux:accordion.heading>Do you offer bulk discounts?</flux:accordion.heading>
        <flux:accordion.content>
            Yes, we offer special discounts for bulk orders. Please contact our sales team.
        </flux:accordion.content>
    </flux:accordion.item>
</flux:accordion>
```

---

## API Reference

### `<flux:accordion>` Props

| Prop | Type | Description |
|------|------|-------------|
| `variant` | `"reverse"` | Display icon before heading instead of after |
| `transition` | `boolean` | Enable smooth expanding transitions (default: `false`) |
| `exclusive` | `boolean` | Only one item expanded at a time (default: `false`) |

### `<flux:accordion.item>` Props

| Prop | Type | Description |
|------|------|-------------|
| `heading` | `string` | Shorthand for heading text (alternative to `<flux:accordion.heading>`) |
| `expanded` | `boolean` | Expanded by default (default: `false`) |
| `disabled` | `boolean` | Prevent expand/collapse (default: `false`) |

### `<flux:accordion.heading>` Slot

| Slot | Description |
|------|-------------|
| `default` | The heading text content |

### `<flux:accordion.content>` Slot

| Slot | Description |
|------|-------------|
| `default` | Content displayed when item is expanded |

---

## Common Patterns

### Shorthand Syntax
```blade
<flux:accordion>
    <flux:accordion.item heading="What's your refund policy?">
        If you are not satisfied with your purchase, we offer a 30-day money-back guarantee.
    </flux:accordion.item>
</flux:accordion>
```

### Exclusive Mode (Single Item Expanded)
```blade
<flux:accordion exclusive>
    <flux:accordion.item heading="Question 1">Answer 1</flux:accordion.item>
    <flux:accordion.item heading="Question 2">Answer 2</flux:accordion.item>
</flux:accordion>
```

### With Smooth Transitions
```blade
<flux:accordion transition>
    <flux:accordion.item heading="Question">Answer</flux:accordion.item>
</flux:accordion>
```

### Default Expanded Item
```blade
<flux:accordion>
    <flux:accordion.item heading="Important Info" expanded>
        This section is expanded by default.
    </flux:accordion.item>
    <flux:accordion.item heading="Other Info">
        This is collapsed by default.
    </flux:accordion.item>
</flux:accordion>
```

### Disabled Item
```blade
<flux:accordion>
    <flux:accordion.item heading="Available Section">Content here</flux:accordion.item>
    <flux:accordion.item heading="Coming Soon" disabled>
        This section cannot be expanded.
    </flux:accordion.item>
</flux:accordion>
```

### Leading Icon (Reverse Variant)
```blade
<flux:accordion variant="reverse">
    <flux:accordion.item heading="Question">Answer</flux:accordion.item>
</flux:accordion>
```

---

## Usage Guidelines

- **FAQs**: Perfect for frequently asked questions sections
- **Long content**: Break up lengthy content into digestible sections
- **Exclusive mode**: Use when only one section should be visible at a time
- **Transitions**: Enable for smoother user experience (slight performance cost)
- **Default expanded**: Highlight important sections by expanding them by default
- **Disabled items**: Use for "coming soon" or restricted content

---

**Reference**: [Flux Accordion Documentation](https://fluxui.dev/components/accordion)
