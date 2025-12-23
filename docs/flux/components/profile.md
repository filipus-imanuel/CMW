# Flux Profile Component

Display a user's profile with an avatar and optional name in a compact, interactive component. Typically used in headers for user menus.

## Basic Usage

```blade
<!-- Avatar only -->
<flux:profile avatar="https://unavatar.io/x/calebporzio" />

<!-- With name -->
<flux:profile 
    name="Caleb Porzio" 
    avatar="https://unavatar.io/x/calebporzio" 
/>
```

## Chevron Control

```blade
<!-- Without chevron (hide dropdown indicator) -->
<flux:profile 
    :chevron="false" 
    avatar="https://unavatar.io/x/calebporzio" 
/>

<!-- Custom trailing icon -->
<flux:profile 
    icon:trailing="chevron-up-down"
    avatar="https://unavatar.io/x/calebporzio"
    name="Caleb Porzio"
/>
```

## Avatar Styles

```blade
<!-- Circle avatar -->
<flux:profile 
    circle 
    name="Caleb Porzio" 
    avatar="https://unavatar.io/x/calebporzio" 
/>

<!-- Square avatar (default) -->
<flux:profile 
    name="Caleb Porzio" 
    avatar="https://unavatar.io/x/calebporzio" 
/>
```

## Avatar with Initials

When no avatar image is provided, initials are automatically generated:

```blade
<!-- Auto-generate from name -->
<flux:profile name="Caleb Porzio" />

<!-- With custom color -->
<flux:profile name="Caleb Porzio" avatar:color="cyan" />

<!-- Manual initials -->
<flux:profile initials="CP" />

<!-- Name only for avatar initial generation -->
<flux:profile avatar:name="Caleb Porzio" />
```

## Common Patterns

### Profile Menu (Dropdown)

```blade
<flux:dropdown align="end">
    <flux:profile avatar="https://unavatar.io/x/calebporzio" />
    
    <flux:navmenu class="max-w-[12rem]">
        <div class="px-2 py-1.5">
            <flux:text size="sm">Signed in as</flux:text>
            <flux:heading class="mt-1! truncate">caleb@example.com</flux:heading>
        </div>
        
        <flux:navmenu.separator />
        
        <div class="px-2 py-1.5">
            <flux:text size="sm" class="pl-7">Teams</flux:text>
        </div>
        <flux:navmenu.item href="#" icon="check" class="text-zinc-800 dark:text-white truncate">
            Personal
        </flux:navmenu.item>
        <flux:navmenu.item href="#" indent class="text-zinc-800 dark:text-white truncate">
            Acme Inc.
        </flux:navmenu.item>
        
        <flux:navmenu.separator />
        
        <flux:navmenu.item href="/dashboard" icon="key" class="text-zinc-800 dark:text-white">
            Licenses
        </flux:navmenu.item>
        <flux:navmenu.item href="/account" icon="user" class="text-zinc-800 dark:text-white">
            Account
        </flux:navmenu.item>
        
        <flux:navmenu.separator />
        
        <flux:navmenu.item href="/logout" icon="arrow-right-start-on-rectangle" class="text-zinc-800 dark:text-white">
            Logout
        </flux:navmenu.item>
    </flux:navmenu>
</flux:dropdown>
```

### Profile Switcher

```blade
<flux:dropdown position="top" align="start">
    <flux:profile 
        avatar="https://unavatar.io/x/calebporzio" 
        name="Caleb Porzio" 
    />
    
    <flux:menu>
        <flux:menu.radio.group>
            <flux:menu.radio checked>Caleb Porzio</flux:menu.radio>
            <flux:menu.radio>Hugo Sainte-Marie</flux:menu.radio>
            <flux:menu.radio>Josh Hanley</flux:menu.radio>
        </flux:menu.radio.group>
        
        <flux:menu.separator />
        
        <flux:menu.item icon="arrow-right-start-on-rectangle">
            Logout
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

## API Reference

### `<flux:profile>`

**Props:**
- `name` - User's name to display next to avatar (string)
- `avatar` - URL to avatar image (string)
- `avatar:name` - Name for avatar initial generation (string)
- `avatar:color` - Avatar background color (see Avatar color options)
- `circle` - Display circular avatar (boolean, default: `false`)
- `initials` - Custom initials when no avatar image (string, auto-generated from name if not provided)
- `chevron` - Show chevron dropdown indicator (boolean, default: `true`)
- `icon:trailing` - Custom trailing icon instead of chevron (string)
- `icon:variant` - Trailing icon variant: `micro` (default), `outline`

**Slots:**
- `avatar` - Custom avatar content (typically `flux:avatar` component)

## Guidelines

- **Header usage**: Typically placed in header layout for user navigation
- **Dropdown wrapper**: Wrap with `flux:dropdown` for menu functionality
- **Truncate text**: Use `class="truncate"` on name/email for long content
- **Circle vs square**: Circle avatars work better for profile photos; square for branded icons
- **Initials fallback**: Always provide `name` or `initials` for graceful fallback
- **Menu alignment**: Use `align="end"` for header dropdowns to prevent overflow

**Reference:** https://fluxui.dev/components/profile
