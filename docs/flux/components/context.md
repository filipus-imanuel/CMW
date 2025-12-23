# Context Menu Component

Dropdown menus that open when right-clicking a designated area.

## Basic Usage

```blade
<flux:context>
    <flux:card class="border-dashed border-2 px-16">
        <flux:text>Right click</flux:text>
    </flux:card>
    
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
</flux:context>
```

## Structure

The `flux:context` component requires two children:
1. **First child**: Trigger area that shows context menu on right-click
2. **Second child**: `flux:menu` component that appears as the context menu

## Properties

### flux:context

| Property | Type | Description |
|----------|------|-------------|
| `wire:model` | string | Bind context menu state to Livewire property |
| `position` | string | Menu position relative to click: `[vertical] [horizontal]`<br>Vertical: `top`, `bottom` (default)<br>Horizontal: `start`, `center`, `end` (default) |
| `gap` | number | Distance in pixels between menu and click position (default: `4`) |
| `offset` | string | Additional offset along axes: `[x] [y]` |
| `target` | string | ID of external element to use as menu (for DOM outside trigger) |
| `detail` | string | Custom value for `data-detail` attribute (for styling/behavior) |
| `disabled` | bool | Prevent context menu from appearing |

## Menu Content

Context menus use the same `flux:menu` component as dropdowns. See the [Dropdown documentation](https://fluxui.dev/components/dropdown) for full menu options.

**Available menu components:**
- `flux:menu.item` - Single menu item
- `flux:menu.submenu` - Nested submenu
- `flux:menu.separator` - Visual separator
- `flux:menu.radio.group` / `flux:menu.radio` - Radio selection
- `flux:menu.checkbox` - Checkbox items

## Examples

### With Position Control

```blade
<flux:context position="top start" gap="8">
    <div class="p-4 border rounded">Right click me</div>
    
    <flux:menu>
        <flux:menu.item icon="pencil">Edit</flux:menu.item>
        <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
    </flux:menu>
</flux:context>
```

### With Livewire State

```blade
<flux:context wire:model="contextMenuOpen">
    <flux:table.row>
        <flux:table.cell>Right click row</flux:table.cell>
    </flux:table.row>
    
    <flux:menu>
        <flux:menu.item wire:click="edit({{ $id }})" icon="pencil">Edit</flux:menu.item>
        <flux:menu.item wire:click="duplicate({{ $id }})" icon="document-duplicate">Duplicate</flux:menu.item>
        <flux:menu.separator />
        <flux:menu.item wire:click="delete({{ $id }})" variant="danger" icon="trash">Delete</flux:menu.item>
    </flux:menu>
</flux:context>
```

### Disabled Context Menu

```blade
<flux:context disabled>
    <div class="p-4 bg-gray-100">Context menu disabled</div>
    
    <flux:menu>
        <flux:menu.item>Hidden menu</flux:menu.item>
    </flux:menu>
</flux:context>
```

## Guidelines

- First child element becomes the right-click trigger area
- Use `flux:menu` as second child with same structure as dropdown menus
- Position defaults to `bottom end` (below and aligned to right of click)
- Adjust `gap` to control distance from cursor
- Use `disabled` to conditionally prevent context menu
- Combine with Livewire actions for interactive menus
- Add `variant="danger"` to destructive actions (delete, remove)

**Reference**: https://fluxui.dev/components/context
