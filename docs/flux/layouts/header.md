# Flux Header Layout

## Basic Structure
```blade
<flux:header sticky container>
    <flux:sidebar.toggle icon="bars-2" inset="left" class="lg:hidden" />

    <flux:brand href="/" logo="/img/logo.png" name="Acme Inc." />

    <flux:navbar>
        <flux:navbar.item icon="home" href="/">Home</flux:navbar.item>
        <flux:navbar.item icon="users" href="/users">Users</flux:navbar.item>
        <flux:navbar.item icon="document-text" href="/docs">Docs</flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    <flux:navbar>
        <flux:navbar.item icon="cog-6-tooth" href="/settings">Settings</flux:navbar.item>
    </flux:navbar>

    <flux:profile avatar="/img/avatar.jpg" name="John Doe" />
</flux:header>

<flux:main container>
    <!-- Main content here -->
</flux:main>
```

## With Secondary Sidebar
Combine header with sidebar for dual navigation:

```blade
<flux:header sticky>
    <flux:sidebar.toggle icon="bars-2" inset="left" class="lg:hidden" />
    
    <flux:brand href="/" logo="/img/logo.png" name="Acme Inc." />
    
    <flux:navbar>
        <flux:navbar.item href="/">Dashboard</flux:navbar.item>
        <flux:navbar.item href="/projects">Projects</flux:navbar.item>
    </flux:navbar>
    
    <flux:spacer />
    
    <flux:profile avatar="/img/avatar.jpg" name="John Doe" />
</flux:header>

<flux:sidebar collapsible="mobile">
    <flux:sidebar.nav>
        <flux:sidebar.item href="/overview">Overview</flux:sidebar.item>
        <flux:sidebar.item href="/analytics">Analytics</flux:sidebar.item>
    </flux:sidebar.nav>
</flux:sidebar>

<flux:main>
    <!-- Content -->
</flux:main>
```

## Sticky Header
```blade
<!-- Sticky header that stays at top when scrolling -->
<flux:header sticky>
    <!-- header content -->
</flux:header>
```

## Container Width
```blade
<!-- Constrain header content to container width -->
<flux:header container>
    <!-- header content -->
</flux:header>

<!-- Combine both -->
<flux:header sticky container>
    <!-- header content -->
</flux:header>
```

## Mobile Toggle Button
```blade
<!-- Show toggle button only on mobile -->
<flux:sidebar.toggle icon="bars-2" inset="left" class="lg:hidden" />
```

## Component Reference

### `<flux:header>`
**Props:**
- `sticky` - Makes header sticky when scrolling
- `container` - Constrains header content to container width

**Slots:**
- `default` - Header content (branding, navigation, profile)

**Classes:**
- Common uses: `bg-zinc-50`, `border-b`, `sticky`

### `<flux:sidebar.toggle>`
**Props:**
- `icon` - Icon to display (e.g., `bars-2`, `x-mark`)
- `inset` - Position (e.g., `"left"`)

**Classes:**
- Common use: `lg:hidden` to show only on mobile

### `<flux:main>`
**Props:**
- `container` - Constrains main content to container width

**Slots:**
- `default` - Main content area

## Usage Guidelines
- Use `sticky` prop to keep header visible when scrolling
- Add `container` to both header and main for consistent width alignment
- Combine with `<flux:navbar>` for horizontal navigation items
- Use `<flux:spacer />` to push items to the right side
- Include `<flux:sidebar.toggle>` with `class="lg:hidden"` for mobile menu
- Combine with `<flux:sidebar>` for secondary navigation
- Header layout is ideal for applications with primary horizontal navigation
- Use `<flux:brand>` component for logo and app name

**Reference:** https://fluxui.dev/layouts/header
