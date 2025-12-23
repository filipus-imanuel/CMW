# Breadcrumbs Component

Help users navigate and understand their place within your application.

## Basic Usage

```blade
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
    <flux:breadcrumbs.item href="#">Blog</flux:breadcrumbs.item>
    <flux:breadcrumbs.item>Post</flux:breadcrumbs.item>
</flux:breadcrumbs>
```

## With Slashes

Use slashes instead of chevrons to separate items:

```blade
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="#" separator="slash">Home</flux:breadcrumbs.item>
    <flux:breadcrumbs.item href="#" separator="slash">Blog</flux:breadcrumbs.item>
    <flux:breadcrumbs.item separator="slash">Post</flux:breadcrumbs.item>
</flux:breadcrumbs>
```

## With Icon

Use an icon instead of text for a breadcrumb item:

```blade
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="#" icon="home" />
    <flux:breadcrumbs.item href="#">Blog</flux:breadcrumbs.item>
    <flux:breadcrumbs.item>Post</flux:breadcrumbs.item>
</flux:breadcrumbs>
```

## With Ellipsis

Truncate a long breadcrumb list with an ellipsis:

```blade
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="#" icon="home" />
    <flux:breadcrumbs.item icon="ellipsis-horizontal" />
    <flux:breadcrumbs.item>Post</flux:breadcrumbs.item>
</flux:breadcrumbs>
```

## With Ellipsis Dropdown

Truncate a long breadcrumb list into a dropdown:

```blade
<flux:breadcrumbs>
    <flux:breadcrumbs.item href="#" icon="home" />
    <flux:breadcrumbs.item>
        <flux:dropdown>
            <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
            <flux:navmenu>
                <flux:navmenu.item>Client</flux:navmenu.item>
                <flux:navmenu.item icon="arrow-turn-down-right">Team</flux:navmenu.item>
                <flux:navmenu.item icon="arrow-turn-down-right">User</flux:navmenu.item>
            </flux:navmenu>
        </flux:dropdown>
    </flux:breadcrumbs.item>
    <flux:breadcrumbs.item>Post</flux:breadcrumbs.item>
</flux:breadcrumbs>
```

## Properties

### flux:breadcrumbs

| Property | Type | Description |
|----------|------|-------------|
| `default` | slot | The breadcrumb items to display |

### flux:breadcrumbs.item

| Property | Type | Description |
|----------|------|-------------|
| `href` | string | URL the item links to (omit for non-clickable text) |
| `icon` | string | Name of the icon to display before the text |
| `icon:variant` | string | Icon variant: `outline`, `solid`, `mini`, `micro` (default: `mini`) |
| `separator` | string | Icon name for separator (default: `chevron-right`) |

## Guidelines

- Last item should omit `href` to indicate current page
- Use `icon="home"` for home/root item (common pattern)
- Use `separator="slash"` for alternative visual style
- Truncate long paths with ellipsis or dropdown for better UX
- Default separator is `chevron-right`

**Reference**: https://fluxui.dev/components/breadcrumbs
