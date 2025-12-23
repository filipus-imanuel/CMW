# Dropdown Component

A composable dropdown component that handles both simple navigation menus and complex action menus with checkboxes, radios, and submenus.

## Basic Usage

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Options</flux:button>
    
    <flux:menu>
        <flux:menu.item icon="plus">New post</flux:menu.item>
        
        <flux:menu.separator />
        
        <flux:menu.submenu heading="Sort by">
            <flux:menu.radio.group>
                <flux:menu.radio checked>Name</flux:menu.radio>
                <flux:menu.radio>Date</flux:menu.radio>
                <flux:menu.radio>Popularity</flux:menu.radio>
            </flux:menu.radio.group>
        </flux:menu.submenu>
        
        <flux:menu.submenu heading="Filter">
            <flux:menu.checkbox checked>Draft</flux:menu.checkbox>
            <flux:menu.checkbox checked>Published</flux:menu.checkbox>
            <flux:menu.checkbox>Archived</flux:menu.checkbox>
        </flux:menu.submenu>
        
        <flux:menu.separator />
        
        <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

## Navigation Menus

Use `flux:navmenu` for simple link collections (no keyboard navigation or submenus):

```blade
<flux:dropdown position="bottom" align="end">
    <flux:profile avatar="/img/demo/user.png" name="Olivia Martin" />
    
    <flux:navmenu>
        <flux:navmenu.item href="#" icon="user">Account</flux:navmenu.item>
        <flux:navmenu.item href="#" icon="building-storefront">Profile</flux:navmenu.item>
        <flux:navmenu.item href="#" icon="credit-card">Billing</flux:navmenu.item>
        <flux:navmenu.item href="#" icon="arrow-right-start-on-rectangle">Logout</flux:navmenu.item>
        <flux:navmenu.item href="#" icon="trash" variant="danger">Delete</flux:navmenu.item>
    </flux:navmenu>
</flux:dropdown>
```

## Positioning

Customize position and alignment:

```blade
<!-- Position: top, bottom (default), left, right -->
<!-- Align: start (default), center, end -->
<flux:dropdown position="top" align="start">
    <flux:button>Click me</flux:button>
    <flux:menu>
        <!-- items -->
    </flux:menu>
</flux:dropdown>

<flux:dropdown position="right" align="center">
    <!-- items -->
</flux:dropdown>

<flux:dropdown position="bottom" align="end">
    <!-- items -->
</flux:dropdown>
```

## Offset & Gap

```blade
<!-- Offset and gap in pixels -->
<flux:dropdown offset="-15" gap="2">
    <flux:button>Options</flux:button>
    <flux:menu>
        <!-- items -->
    </flux:menu>
</flux:dropdown>
```

## Menu Features

### Keyboard Hints

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Options</flux:button>
    <flux:menu>
        <flux:menu.item icon="pencil-square" kbd="⌘S">Save</flux:menu.item>
        <flux:menu.item icon="document-duplicate" kbd="⌘D">Duplicate</flux:menu.item>
        <flux:menu.item icon="trash" variant="danger" kbd="⌘⌫">Delete</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

### Checkbox Items

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Permissions</flux:button>
    <flux:menu>
        <flux:menu.checkbox wire:model="read" checked>Read</flux:menu.checkbox>
        <flux:menu.checkbox wire:model="write" checked>Write</flux:menu.checkbox>
        <flux:menu.checkbox wire:model="delete">Delete</flux:menu.checkbox>
    </flux:menu>
</flux:dropdown>
```

### Radio Items

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Sort by</flux:button>
    <flux:menu>
        <flux:menu.radio.group wire:model="sortBy">
            <flux:menu.radio checked>Latest activity</flux:menu.radio>
            <flux:menu.radio>Date created</flux:menu.radio>
            <flux:menu.radio>Most popular</flux:menu.radio>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
```

### Groups with Separators

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Options</flux:button>
    <flux:menu>
        <flux:menu.item>View</flux:menu.item>
        <flux:menu.item>Transfer</flux:menu.item>
        
        <flux:menu.separator />
        
        <flux:menu.item>Publish</flux:menu.item>
        <flux:menu.item>Share</flux:menu.item>
        
        <flux:menu.separator />
        
        <flux:menu.item variant="danger">Delete</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

### Groups with Headings

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Options</flux:button>
    <flux:menu>
        <flux:menu.group heading="Account">
            <flux:menu.item>Profile</flux:menu.item>
            <flux:menu.item>Permissions</flux:menu.item>
        </flux:menu.group>
        
        <flux:menu.group heading="Billing">
            <flux:menu.item>Transactions</flux:menu.item>
            <flux:menu.item>Payouts</flux:menu.item>
            <flux:menu.item>Refunds</flux:menu.item>
        </flux:menu.group>
        
        <flux:menu.item>Logout</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

### Submenus

```blade
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Options</flux:button>
    <flux:menu>
        <flux:menu.submenu heading="Sort by">
            <flux:menu.radio checked>Name</flux:menu.radio>
            <flux:menu.radio>Date</flux:menu.radio>
            <flux:menu.radio>Popularity</flux:menu.radio>
        </flux:menu.submenu>
        
        <flux:menu.submenu heading="Filter">
            <flux:menu.checkbox checked>Draft</flux:menu.checkbox>
            <flux:menu.checkbox checked>Published</flux:menu.checkbox>
            <flux:menu.checkbox>Archived</flux:menu.checkbox>
        </flux:menu.submenu>
        
        <flux:menu.separator />
        
        <flux:menu.item variant="danger">Delete</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

## Keep Open

Prevent menu from closing when items are clicked:

```blade
<!-- Keep menu open for all items -->
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Filter</flux:button>
    <flux:menu keep-open>
        <flux:menu.checkbox checked>Draft</flux:menu.checkbox>
        <flux:menu.checkbox checked>Published</flux:menu.checkbox>
        <flux:menu.checkbox>Archived</flux:menu.checkbox>
    </flux:menu>
</flux:dropdown>

<!-- Keep open for specific items only -->
<flux:dropdown>
    <flux:button icon:trailing="chevron-down">Filters</flux:button>
    <flux:menu>
        <flux:menu.checkbox keep-open checked>Draft</flux:menu.checkbox>
        <flux:menu.checkbox keep-open checked>Published</flux:menu.checkbox>
        <flux:menu.checkbox keep-open>Archived</flux:menu.checkbox>
        
        <flux:menu.separator />
        
        <flux:menu.item variant="danger">Clear</flux:menu.item>
    </flux:menu>
</flux:dropdown>
```

**Supports `keep-open` on:**
- `flux:menu.item`
- `flux:menu.checkbox`
- `flux:menu.radio`
- `flux:menu.radio.group`
- `flux:menu.submenu`

## Properties

### flux:dropdown

| Property | Description |
|----------|-------------|
| `position` | Position: `top`, `right`, `bottom` (default), `left` |
| `align` | Alignment: `start` (default), `center`, `end` |
| `offset` | Offset in pixels from trigger (default: 0) |
| `gap` | Gap in pixels between trigger and menu (default: 4) |

### flux:menu

Complex menu with keyboard navigation, submenus, checkboxes, and radios.

| Property | Description |
|----------|-------------|
| `keep-open` | Prevent menu from closing when any item is clicked |

### flux:menu.item

| Property | Description |
|----------|-------------|
| `icon` | Icon at start of item |
| `icon:trailing` | Icon at end of item |
| `icon:variant` | Icon style: `outline`, `solid`, `mini`, `micro` |
| `kbd` | Keyboard shortcut hint (e.g., `⌘S`) |
| `suffix` | Text at end of item |
| `variant` | Style: `default`, `danger` |
| `disabled` | Prevent interaction |
| `keep-open` | Prevent menu from closing when this item is clicked |

### flux:menu.submenu

| Property | Description |
|----------|-------------|
| `heading` | Submenu heading text |
| `icon` | Icon at start |
| `icon:trailing` | Icon at end |
| `icon:variant` | Icon style: `outline`, `solid`, `mini`, `micro` |
| `keep-open` | Prevent menu from closing when submenu items are clicked |

### flux:menu.separator

Horizontal line separator. No props.

### flux:menu.checkbox

| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property |
| `checked` | Checked by default |
| `disabled` | Prevent interaction |
| `keep-open` | Prevent menu from closing when clicked |

### flux:menu.radio.group

| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property |
| `keep-open` | Prevent menu from closing when radios are clicked |

### flux:menu.radio

| Property | Description |
|----------|-------------|
| `checked` | Selected by default |
| `disabled` | Prevent interaction |
| `keep-open` | Prevent menu from closing when clicked |

## Guidelines

- Use `flux:navmenu` for simple link collections
- Use `flux:menu` for action menus with keyboard navigation/submenus
- First child of `flux:dropdown` is the trigger element
- Second child is the menu content (`flux:menu` or `flux:navmenu`)
- Use `variant="danger"` for destructive actions
- Add `kbd` attribute to teach keyboard shortcuts
- Use `keep-open` for filters/multi-select scenarios
- Default position is `bottom` with `start` alignment

**Reference**: https://fluxui.dev/components/dropdown
