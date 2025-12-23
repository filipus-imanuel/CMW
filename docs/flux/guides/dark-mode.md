# Flux UI Dark Mode

Flux supports dark mode out of the box with automatic system preference detection and user preference persistence.

## Tailwind Setup

Configure Tailwind to use selector strategy for dark mode:

```css
/* resources/css/app.css */

@import "tailwindcss";
@import '../../vendor/livewire/flux/dist/flux.css';

@custom-variant dark (&:where(.dark, .dark *));
```

This allows Flux to toggle dark mode by adding/removing `.dark` class on `<html>` element.

## Automatic Dark Mode Handling

By default, Flux automatically manages appearance by:
- Adding `.dark` class based on system preference or user selection
- Storing user preference in localStorage
- Listening for system preference changes

**Requires:** `@fluxAppearance` directive in layout file

```blade
<head>
    ...
    @fluxAppearance
</head>
```

### Disable Automatic Handling

Remove `@fluxAppearance` to manage dark mode manually:

```blade
<head>
    ...
    <!-- Remove @fluxAppearance to handle dark mode manually -->
</head>
```

## JavaScript Utilities

Flux provides utilities for managing dark mode without manual complexity:

### Alpine.js Usage

```javascript
// Get/set user's color scheme preference
$flux.appearance = 'light|dark|system'

// Get/set current color scheme (actual dark mode state)
$flux.dark = true|false
```

### Vanilla JavaScript Usage

```javascript
// Access global Flux object
let button = document.querySelector('...');
button.addEventListener('click', () => {
    Flux.dark = !Flux.dark;
});
```

## Implementation Examples

### Toggle Button
Simple button to toggle dark mode:

```blade
<flux:button 
    x-data 
    x-on:click="$flux.dark = !$flux.dark" 
    icon="moon" 
    variant="subtle" 
    aria-label="Toggle dark mode" 
/>
```

### Dropdown Menu
Robust menu with light/dark/system options:

```blade
<flux:dropdown x-data align="end">
    <flux:button variant="subtle" square class="group" aria-label="Preferred color scheme">
        <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini" class="text-zinc-500 dark:text-white" />
        <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini" class="text-zinc-500 dark:text-white" />
        <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
        <flux:icon.sun x-show="$flux.appearance === 'system' && !$flux.dark" variant="mini" />
    </flux:button>

    <flux:menu>
        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

### Toggle Switch
Simple switch for settings pages:

```blade
<flux:switch x-data x-model="$flux.dark" label="Dark mode" />
```

### Segmented Radio (with labels)
```blade
<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
    <flux:radio value="light" icon="sun">Light</flux:radio>
    <flux:radio value="dark" icon="moon">Dark</flux:radio>
    <flux:radio value="system" icon="computer-desktop">System</flux:radio>
</flux:radio.group>
```

### Segmented Radio (icon-only)
Saves horizontal space:

```blade
<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
    <flux:radio value="light" icon="sun" />
    <flux:radio value="dark" icon="moon" />
    <flux:radio value="system" icon="computer-desktop" />
</flux:radio.group>
```

## Key Concepts

**Two properties:**
- `$flux.appearance` - User preference: `'light'`, `'dark'`, or `'system'`
- `$flux.dark` - Actual dark mode state: `true` or `false`

**When `appearance = 'system'`:**
- `$flux.dark` follows system preference automatically
- Flux listens for system preference changes

**Automatic behaviors handled by Flux:**
- Add/remove `.dark` class on `<html>`
- Store preference in localStorage
- Honor system preference when `'system'` selected
- Listen for system preference changes after page load

## Usage Guidelines

- Use dropdown menu for most flexible user control
- Use toggle button for simple on/off in navbar/sidebar
- Use toggle switch in settings pages
- Use segmented radio for visual clarity
- Always include `x-data` directive on control elements
- Use `aria-label` for accessibility on icon-only buttons

**Reference:** https://fluxui.dev/docs/dark-mode
