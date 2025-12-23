# Navbar Component (Flux Pro)

Arrange navigation links vertically (sidebar) or horizontally (header).

---

## Horizontal Navbar

Basic horizontal navigation bar.

```blade
<flux:navbar>
    <flux:navbar.item href="#">Home</flux:navbar.item>
    <flux:navbar.item href="#">Features</flux:navbar.item>
    <flux:navbar.item href="#">Pricing</flux:navbar.item>
    <flux:navbar.item href="#">About</flux:navbar.item>
</flux:navbar>
```

---

## Vertical Navlist (Sidebar)

Vertical navigation for sidebars.

```blade
<flux:navlist class="w-64">
    <flux:navlist.item href="#" icon="home">Home</flux:navlist.item>
    <flux:navlist.item href="#" icon="puzzle-piece">Features</flux:navlist.item>
    <flux:navlist.item href="#" icon="currency-dollar">Pricing</flux:navlist.item>
    <flux:navlist.item href="#" icon="user">About</flux:navlist.item>
</flux:navlist>
```

---

## Current Page Detection

Auto-detects current page based on `href`, or manually set with `current` prop.

```blade
<!-- Auto-detect -->
<flux:navbar.item href="/">Home</flux:navbar.item>

<!-- Manual override -->
<flux:navbar.item href="/" current>Home</flux:navbar.item>
<flux:navbar.item href="/" :current="false">Home</flux:navbar.item>
<flux:navbar.item href="/" :current="request()->is('/')">Home</flux:navbar.item>
```

---

## With Icons

Add leading icons for visual context.

```blade
<flux:navbar>
    <flux:navbar.item href="#" icon="home">Home</flux:navbar.item>
    <flux:navbar.item href="#" icon="puzzle-piece">Features</flux:navbar.item>
    <flux:navbar.item href="#" icon="currency-dollar">Pricing</flux:navbar.item>
    <flux:navbar.item href="#" icon="user">About</flux:navbar.item>
</flux:navbar>
```

---

## With Badges

Add trailing badges to items.

```blade
<flux:navbar>
    <flux:navbar.item href="#">Home</flux:navbar.item>
    <flux:navbar.item href="#" badge="12">Inbox</flux:navbar.item>
    <flux:navbar.item href="#">Contacts</flux:navbar.item>
    <flux:navbar.item href="#" badge="Pro" badge:color="lime">Calendar</flux:navbar.item>
</flux:navbar>
```

---

## Dropdown Navigation

Group related items in a dropdown.

```blade
<flux:navbar>
    <flux:navbar.item href="#">Dashboard</flux:navbar.item>
    <flux:navbar.item href="#">Transactions</flux:navbar.item>
    
    <flux:dropdown>
        <flux:navbar.item icon:trailing="chevron-down">Account</flux:navbar.item>
        <flux:navmenu>
            <flux:navmenu.item href="#">Profile</flux:navmenu.item>
            <flux:navmenu.item href="#">Settings</flux:navmenu.item>
            <flux:navmenu.item href="#">Billing</flux:navmenu.item>
        </flux:navmenu>
    </flux:dropdown>
</flux:navbar>
```

---

## Navlist Groups

Group related navigation items.

```blade
<flux:navlist>
    <flux:navlist.group heading="Account" class="mt-4">
        <flux:navlist.item href="#">Profile</flux:navlist.item>
        <flux:navlist.item href="#">Settings</flux:navlist.item>
        <flux:navlist.item href="#">Billing</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
```

---

## Collapsible Groups

Make groups expandable/collapsible.

```blade
<flux:navlist class="w-64">
    <flux:navlist.item href="#" icon="home">Dashboard</flux:navlist.item>
    <flux:navlist.item href="#" icon="list-bullet">Transactions</flux:navlist.item>
    
    <!-- Expandable group (collapsed by default) -->
    <flux:navlist.group heading="Account" expandable :expanded="false">
        <flux:navlist.item href="#">Profile</flux:navlist.item>
        <flux:navlist.item href="#">Settings</flux:navlist.item>
        <flux:navlist.item href="#">Billing</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
```

---

## Navlist with Badges

```blade
<flux:navlist class="w-64">
    <flux:navlist.item href="#" icon="home">Home</flux:navlist.item>
    <flux:navlist.item href="#" icon="envelope" badge="12">Inbox</flux:navlist.item>
    <flux:navlist.item href="#" icon="user-group">Contacts</flux:navlist.item>
    <flux:navlist.item href="#" icon="calendar-days" badge="Pro" badge:color="lime">
        Calendar
    </flux:navlist.item>
</flux:navlist>
```

---

## API Reference

### flux:navbar
Horizontal navigation container.

**Slots:**
- `default` - The navigation items

**Attributes:**
- `data-flux-navbar` - Applied to root element

---

### flux:navbar.item

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `href` | string | - | URL the item links to |
| `current` | boolean | auto-detect | Applies active styling |
| `icon` | string | - | Icon name at start |
| `icon:trailing` | string | - | Icon name at end |
| `badge` | string/boolean | - | Badge content |
| `badge:color` | string | - | Badge color (same as badge component) |
| `badge:variant` | enum | `solid` | `solid`, `outline` |

**Attributes:**
- `data-current` - Applied when item is active

---

### flux:navlist
Vertical navigation container (sidebar).

**Slots:**
- `default` - The navigation items and groups

**Attributes:**
- `data-flux-navlist` - Applied to root element

---

### flux:navlist.item

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `href` | string | - | URL the item links to |
| `current` | boolean | auto-detect | Applies active styling |
| `icon` | string | - | Icon name at start |
| `badge` | string/boolean | - | Badge content |
| `badge:color` | string | - | Badge color |
| `badge:variant` | enum | `solid` | `solid`, `outline` |

**Attributes:**
- `data-current` - Applied when item is active

---

### flux:navlist.group

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `heading` | string | - | Group heading text |
| `expandable` | boolean | `false` | Makes group collapsible |
| `expanded` | boolean | `true` | Expands by default (if expandable) |

**Slots:**
- `default` - The group's navigation items

---

## Usage Guidelines

**Navbar (Horizontal):**
- Use in headers/top navigation
- Best for 4-8 primary items
- Dropdown for secondary items

**Navlist (Sidebar):**
- Use in side panels
- Supports unlimited items
- Groups for logical sections
- Icons recommended for clarity

**Current Page:**
- Auto-detected by URL match
- Override with `:current` when needed
- Use for custom routing logic

**Badges:**
- Notification counts (numeric)
- Status labels (text)
- Feature flags ("Pro", "New")

---

**Reference:** https://fluxui.dev/components/navbar
