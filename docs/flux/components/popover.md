# Flux Popover Component

Show extra content in a popup on click or hover. Use only if the Dropdown menu component doesn't fit your needs.

## Basic Usage

```blade
<flux:dropdown>
    <flux:button icon="adjustments-horizontal">Options</flux:button>
    
    <flux:popover class="flex flex-col gap-4">
        <!-- Popover content here -->
    </flux:popover>
</flux:dropdown>
```

## Hover Trigger

Add `hover` prop to show popover on hover instead of click:

```blade
<flux:dropdown hover position="bottom" align="start">
    <button type="button" class="flex items-center gap-3">
        <flux:avatar size="sm" name="John Doe" />
        <flux:heading>John Doe</flux:heading>
    </button>
    
    <flux:popover class="flex flex-col gap-3 rounded-xl shadow-xl">
        <!-- User card content -->
    </flux:popover>
</flux:dropdown>
```

## Position & Alignment

Control popover placement using `position` and `align` props on `flux:dropdown`:

```blade
<!-- Position: top, right, bottom (default), left -->
<flux:dropdown position="top" align="start">
    <flux:button>Top Start</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>

<!-- Align: start (default), center, end -->
<flux:dropdown position="bottom" align="center">
    <flux:button>Bottom Center</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>
```

## Gap & Offset

- **gap**: Distance between trigger and popover
- **offset**: Shifts popover along alignment axis

```blade
<flux:dropdown gap="16">
    <flux:button>Gap: 16px</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>

<flux:dropdown offset="32">
    <flux:button>Offset: 32px</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>
```

## Programmatic Control

Bind open/closed state to Livewire property:

```blade
<flux:dropdown wire:model="isOpen">
    <flux:button>Toggle</flux:button>
    <flux:popover>...</flux:popover>
</flux:dropdown>
```

## Common Patterns

### Category Picker with Clear Action

```blade
<flux:dropdown>
    <flux:button icon="tag">
        Categories
        <x-slot name="iconTrailing">
            <flux:badge size="sm">3</flux:badge>
        </x-slot>
    </flux:button>
    
    <flux:popover class="max-w-[18rem] flex flex-col gap-4">
        <flux:checkbox.group variant="pills" wire:model="categories">
            <flux:checkbox value="fantasy" label="Fantasy" />
            <flux:checkbox value="horror" label="Horror" />
        </flux:checkbox.group>
        
        <flux:separator variant="subtle" />
        
        <flux:button variant="subtle" size="sm" wire:click="$set('categories', [])">
            Clear all
        </flux:button>
    </flux:popover>
</flux:dropdown>
```

### Feedback Form

```blade
<flux:dropdown>
    <flux:button icon="chat-bubble-oval-left">Feedback</flux:button>
    
    <flux:popover class="min-w-[30rem] flex flex-col gap-4">
        <flux:radio.group variant="buttons" class="*:flex-1">
            <flux:radio icon="bug-ant" checked>Bug report</flux:radio>
            <flux:radio icon="light-bulb">Suggestion</flux:radio>
        </flux:radio.group>
        
        <flux:textarea rows="8" placeholder="Your feedback..." />
        
        <div class="flex gap-2 justify-end">
            <flux:button variant="filled" size="sm">Cancel</flux:button>
            <flux:button size="sm">Submit</flux:button>
        </div>
    </flux:popover>
</flux:dropdown>
```

## API Reference

### `<flux:dropdown>`

Container component managing popover positioning and interaction. Required wrapper.

**Props:**
- `position` - Position relative to trigger: `top`, `right`, `bottom` (default), `left`
- `align` - Alignment: `start` (default), `center`, `end`
- `gap` - Distance between trigger and popover (string/number)
- `offset` - Shift along alignment axis (string/number)
- `hover` - Open on hover instead of click (boolean)
- `wire:model` - Bind open/closed state to Livewire property

**Slots:**
- `default` - Must contain one trigger element + one `flux:popover`

**Data Attributes:**
- `data-flux-dropdown` - Root element identifier
- `data-open` - Present when popover is open

### `<flux:popover>`

Floating overlay content container. Must be inside `flux:dropdown`.

**Props:**
- `class` - Additional CSS classes (use for width: `max-w-sm`, `w-80`)

**Slots:**
- `default` - Popover content (any HTML/Flux components)

**Data Attributes:**
- `data-flux-popover` - Root element identifier

## Guidelines

- **Width control**: Use `class="max-w-[18rem]"` or `w-80` on `flux:popover`
- **Trigger element**: Currently only `button` works with hover trigger
- **Structure**: Always wrap content in `flux:dropdown` → trigger + `flux:popover`
- **Styling**: Use Flux components inside popover for consistency
- **vs Dropdown menu**: Use popover for custom content; dropdown menu for action lists

**Reference:** https://fluxui.dev/components/popover
