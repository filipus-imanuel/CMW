# Flux Tooltip Component

## Basic Usage
```blade
<!-- Simple tooltip with content prop -->
<flux:tooltip content="Settings">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<!-- Shorthand: tooltip prop on button -->
<flux:button tooltip="Settings" icon="cog-6-tooth" icon:variant="outline" />
```

## Info Tooltip (Toggleable)
For essential content that touch device users need to access via click/tap instead of hover only:

```blade
<flux:heading class="flex items-center gap-2">
    Tax identification number
    <flux:tooltip toggleable>
        <flux:button icon="information-circle" size="sm" variant="ghost" />
        <flux:tooltip.content class="max-w-[20rem] space-y-2">
            <p>For US businesses, enter your 9-digit Employer Identification Number (EIN) without hyphens.</p>
            <p>For European companies, enter your VAT number including the country prefix (e.g., DE123456789).</p>
        </flux:tooltip.content>
    </flux:tooltip>
</flux:heading>
```

## Position Control
```blade
<!-- Top (default) -->
<flux:tooltip content="Settings" position="top">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<!-- Right -->
<flux:tooltip content="Settings" position="right">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<!-- Bottom -->
<flux:tooltip content="Settings" position="bottom">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>

<!-- Left -->
<flux:tooltip content="Settings" position="left">
    <flux:button icon="cog-6-tooth" icon:variant="outline" />
</flux:tooltip>
```

## Disabled Buttons
Pointer events are disabled on disabled buttons, so wrap in a div to show tooltip:

```blade
<flux:tooltip content="Cannot merge until reviewed by a team member">
    <div>
        <flux:button disabled icon="arrow-turn-down-right">
            Merge pull request
        </flux:button>
    </div>
</flux:tooltip>
```

## Component Reference

### `<flux:tooltip>`
**Props:**
- `content` - Text content to display (alternative to using `flux:tooltip.content`)
- `position` - Position relative to trigger: `top` (default), `right`, `bottom`, `left`
- `align` - Alignment: `center` (default), `start`, `end`
- `disabled` - Prevents user interaction
- `gap` - Spacing between trigger and tooltip (default: `5px`)
- `offset` - Offset from trigger element (default: `0px`)
- `toggleable` - Makes tooltip clickable instead of hover-only (essential for touch devices)
- `interactive` - Uses proper ARIA attributes (`aria-expanded`, `aria-controls`) for interactive content
- `kbd` - Keyboard shortcut hint displayed at the end

### `<flux:tooltip.content>`
**Props:**
- `kbd` - Keyboard shortcut hint displayed at the end

## Usage Guidelines
- **Don't rely on tooltips for essential information** - touch devices may not trigger hover states
- Use `toggleable` for important information that touch users need to access
- Wrap disabled buttons in `<div>` to enable tooltip display
- For complex tooltip content, use `<flux:tooltip.content>` instead of `content` prop
- Position tooltips for optimal visibility relative to trigger element

**Reference:** https://fluxui.dev/components/tooltip
