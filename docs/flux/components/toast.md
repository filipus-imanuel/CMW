# Flux Toast Component

## Setup
Include toast component in your layout (usually in `<body>`):

```blade
<body>
    <!-- ... -->
    <flux:toast />
</body>
```

For `wire:navigate` support, persist the component:

```blade
<body>
    <!-- ... -->
    @persist('toast')
        <flux:toast />
    @endpersist
</body>
```

## Basic Usage (Livewire)
```php
<?php
namespace App\Livewire;

use Livewire\Component;
use Flux\Flux;

class EditPost extends Component
{
    public function save()
    {
        // ...
        Flux::toast('Your changes have been saved.');
    }
}
```

## With Heading
```php
Flux::toast(
    heading: 'Changes saved.',
    text: 'You can always update this in your settings.',
);
```

## Variants
```php
Flux::toast(variant: 'success', text: 'Successfully saved!');
Flux::toast(variant: 'warning', text: 'Warning message');
Flux::toast(variant: 'danger', text: 'Error occurred');
```

## Positioning
```blade
<!-- Default: bottom end -->
<flux:toast position="top end" />

<!-- With custom padding (e.g., for navbars) -->
<flux:toast position="top end" class="pt-24" />
```

**Position options:**
- `bottom end` (default), `bottom center`, `bottom start`
- `top end`, `top center`, `top start`

## Duration Control
```php
// Custom duration (1 second)
Flux::toast(duration: 1000, text: 'Quick message');

// Permanent (stays until dismissed)
Flux::toast(duration: 0, text: 'Important notice');
```

Default duration: 5000ms (5 seconds)

## Toast Stack
```blade
<!-- Default: overlapping, expands on hover -->
<flux:toast.group>
    <flux:toast />
</flux:toast.group>

<!-- Always expanded -->
<flux:toast.group expanded>
    <flux:toast />
</flux:toast.group>

<!-- Custom position -->
<flux:toast.group position="top end">
    <flux:toast />
</flux:toast.group>
```

## Alpine.js Usage
```blade
<!-- Simple usage -->
<button x-on:click="$flux.toast('Your changes have been saved.')">
    Save changes
</button>

<!-- Advanced usage -->
<button x-on:click="$flux.toast({
    heading: 'Success!',
    text: 'Your changes have been saved',
    variant: 'success',
    duration: 3000
})">
    Save changes
</button>
```

## JavaScript Usage
```javascript
// Using window.Flux global
let button = document.querySelector('...');
button.addEventListener('click', () => {
    Flux.toast('Your changes have been saved.');
});

// Or with configuration object
Flux.toast({
    heading: 'Changes saved',
    text: 'Your changes have been saved.',
    variant: 'success',
});
```

## Component Reference

### `<flux:toast>`
**Props:**
- `position` - Position on screen: `bottom end` (default), `bottom center`, `bottom start`, `top end`, `top center`, `top start`

### `<flux:toast.group>`
**Props:**
- `position` - Position of toast group (same options as `flux:toast`)
- `expanded` - If `true`, always shows stack expanded (default: `false`)

### `Flux::toast()` (PHP/Livewire)
**Parameters:**
- `heading` - Optional heading text
- `text` - Main content text
- `variant` - Visual style: `success`, `warning`, `danger`
- `duration` - Duration in milliseconds (default: 5000, use 0 for permanent)

### `$flux.toast()` (Alpine.js)
**Usage:**
- Simple: `$flux.toast('message')` - String becomes text content
- Advanced: `$flux.toast({ heading, text, variant, duration })` - Object with full configuration

## Usage Guidelines
- Always include `<flux:toast />` in layout before using toast methods
- Use `@persist` with `wire:navigate` to prevent toast disappearance on navigation
- Use `variant` for contextual feedback (success, warning, danger)
- Set `duration: 0` for critical messages requiring user dismissal
- Position at `top end` is common for desktop apps with fixed headers

**Reference:** https://fluxui.dev/components/toast
