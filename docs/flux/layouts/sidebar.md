# Flux Sidebar Layout

## Basic Structure
```blade
<flux:sidebar sticky>
    <flux:sidebar.header>
        <flux:sidebar.brand href="/" logo="/img/logo.png" name="Acme Inc." />
        <flux:sidebar.collapse />
    </flux:sidebar.header>

    <flux:sidebar.search placeholder="Search..." />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" href="/" current>Dashboard</flux:sidebar.item>
        <flux:sidebar.item icon="users" href="/users">Users</flux:sidebar.item>
        <flux:sidebar.item icon="cog" href="/settings" badge="3">Settings</flux:sidebar.item>
        
        <flux:sidebar.group heading="Reports" expandable>
            <flux:sidebar.item href="/reports/sales">Sales</flux:sidebar.item>
            <flux:sidebar.item href="/reports/analytics">Analytics</flux:sidebar.item>
        </flux:sidebar.group>
        
        <flux:sidebar.spacer />
        
        <flux:sidebar.item icon="question-mark-circle" href="/help">Help</flux:sidebar.item>
    </flux:sidebar.nav>

    <flux:sidebar.profile avatar="/img/avatar.jpg" name="John Doe" />
</flux:sidebar>

<flux:main container>
    <!-- Main content here -->
</flux:main>
```

## Collapsible Sidebar
```blade
<!-- Mobile only (default behavior) -->
<flux:sidebar collapsible="mobile">
    <!-- sidebar content -->
</flux:sidebar>

<!-- Both mobile and desktop -->
<flux:sidebar collapsible>
    <!-- sidebar content -->
</flux:sidebar>

<!-- Never collapsible -->
<flux:sidebar :collapsible="false">
    <!-- sidebar content -->
</flux:sidebar>
```

## With Secondary Header
Combine sidebar with top header for secondary navigation:

```blade
<flux:sidebar>
    <!-- sidebar content -->
</flux:sidebar>

<flux:header>
    <!-- top navigation -->
</flux:header>

<flux:main>
    <!-- content -->
</flux:main>
```

## Mobile Toggle Button
```blade
<!-- Show only on mobile (hidden on lg breakpoint) -->
<flux:sidebar.toggle icon="bars-2" inset="left" class="lg:hidden" />

<flux:sidebar collapsible="mobile">
    <!-- sidebar content -->
</flux:sidebar>
```

## Component Reference

### `<flux:sidebar>`
**Props:**
- `sticky` - Makes sidebar sticky when scrolling
- `collapsible` - Collapse behavior: `"mobile"` (mobile-only), `true` (both), `false` (never)
- `breakpoint` - Viewport breakpoint (default: `1024`, accepts `"1024px"` or `"64rem"`)
- `persist` - Save collapsed state to localStorage (default: `true`)
- `stashable` - **Deprecated**, use `collapsible="mobile"` instead

### `<flux:sidebar.header>`
Container for brand and collapse button.

### `<flux:sidebar.brand>`
**Props:**
- `href` - URL to navigate when clicked
- `logo` - Logo image path/URL
- `logo:dark` - Logo for dark mode
- `name` - Brand name text

### `<flux:sidebar.collapse>`
**Props:**
- `inset` - Position: `"left"`, `"right"`, `"top"`, `"bottom"`, or combinations
- `tooltip` - Tooltip text (default: "Toggle sidebar")

### `<flux:sidebar.search>`
**Props:**
- `placeholder` - Search input placeholder

### `<flux:sidebar.nav>`
Container for navigation items and groups.

### `<flux:sidebar.item>`
**Props:**
- `href` - URL to navigate
- `icon` - Icon before text
- `badge` - Badge value on right side
- `current` - Marks as currently active item
- `tooltip` - Tooltip when sidebar collapsed (defaults to item text)

### `<flux:sidebar.group>`
**Props:**
- `heading` - Group heading text
- `expandable` - Allow expand/collapse
- `icon` - Icon before heading (groups without icon hide when sidebar collapsed)
- `expanded` - Initial expanded state (default: `true`)

### `<flux:sidebar.spacer>`
Adds vertical spacing between sections.

### `<flux:sidebar.profile>`
**Props:**
- `avatar` - User avatar image path/URL
- `name` - User display name

### `<flux:sidebar.toggle>`
**Props:**
- `icon` - Icon to display (e.g., `bars-2`, `x-mark`)
- `inset` - Position (e.g., `"left"`)

**Common usage:** Add `class="lg:hidden"` to show only on mobile.

### `<flux:main>`
**Props:**
- `container` - Constrains content to container width

## Usage Guidelines
- Use `sticky` prop to keep sidebar visible when scrolling
- Set `collapsible="mobile"` for responsive mobile experience
- Mark active navigation item with `current` prop
- Use `<flux:sidebar.group>` with `expandable` for nested navigation
- Add `icon` to groups that should remain visible when sidebar collapsed
- Use `<flux:sidebar.spacer />` to separate navigation sections
- Combine with `<flux:header>` for dual navigation layout
- Set custom `breakpoint` when using non-standard responsive design
- Disable `persist` if you don't want to save collapse state

**Reference:** https://fluxui.dev/layouts/sidebar
