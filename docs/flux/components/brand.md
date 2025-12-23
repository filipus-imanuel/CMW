# Brand Component

Display your company or application's logo and name in a clean, consistent way across your interface.

## Basic Usage

```blade
<!-- With logo image URL -->
<flux:brand href="#" logo="/img/demo/logo.png" name="Acme Inc." />

<!-- With custom logo slot -->
<flux:brand href="#" name="Acme Inc.">
    <x-slot name="logo" class="size-6 rounded shrink-0 bg-accent text-accent-foreground flex items-center justify-center">
        <i class="font-serif font-bold">A</i>
    </x-slot>
</flux:brand>

<!-- Logo only (omit name) -->
<flux:brand href="#" logo="/img/demo/logo.png" />
```

## Logo Slot

Use the `logo` slot to provide custom logo content (SVG, icon, or HTML):

```blade
<flux:brand href="#" name="Launchpad">
    <x-slot name="logo" class="size-6 rounded-full bg-cyan-500 text-white text-xs font-bold">
        <flux:icon name="rocket-launch" variant="micro" />
    </x-slot>
</flux:brand>
```

## Common Use Cases

**Header with brand:**
```blade
<flux:header class="px-4 w-full bg-zinc-50 dark:bg-zinc-800">
    <flux:brand href="#" name="Acme Inc.">
        <x-slot name="logo" class="bg-accent text-accent-foreground">
            <i class="font-serif font-bold">A</i>
        </x-slot>
    </flux:brand>
    
    <flux:navbar variant="outline">
        <flux:navbar.item href="#" current>Home</flux:navbar.item>
        <flux:navbar.item href="#" badge="12">Inbox</flux:navbar.item>
    </flux:navbar>
    
    <flux:spacer />
    <flux:profile circle avatar="https://unavatar.io/x/calebporzio" />
</flux:header>
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `name` | string | Company or application name to display next to the logo |
| `logo` | string | URL to the logo image (or use slot for custom content) |
| `alt` | string | Alternative text for the logo image |
| `href` | string | URL to navigate to when clicked (default: '/') |

## Slots

| Slot | Description |
|------|-------------|
| `logo` | Custom content for the logo section (image, SVG, icon, or HTML) |

## Guidelines

- Use in `<flux:header>` or `<flux:sidebar>` for consistent branding
- Logo slot provides more flexibility than `logo` attribute
- Omit `name` prop to show logo only
- Default `href` is `'/'` - automatically links to homepage

**Reference**: https://fluxui.dev/components/brand
