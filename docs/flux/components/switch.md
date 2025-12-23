# Flux Switch Component

Toggle a setting on or off. Suitable for binary options like enabling or disabling features.

**Use switches as auto-saving controls outside forms; use checkboxes within forms.**

## Basic Usage

```blade
<flux:field variant="inline">
    <flux:label>Enable notifications</flux:label>
    <flux:switch wire:model.live="notifications" />
    <flux:error name="notifications" />
</flux:field>
```

## Within Fieldset

Group related switches within a fieldset:

```blade
<flux:fieldset>
    <flux:legend>Email notifications</flux:legend>
    
    <div class="space-y-4">
        <flux:switch 
            wire:model.live="communication" 
            label="Communication emails" 
            description="Receive emails about your account activity." 
        />
        
        <flux:separator variant="subtle" />
        
        <flux:switch 
            wire:model.live="marketing" 
            label="Marketing emails" 
            description="Receive emails about new products, features, and more." 
        />
        
        <flux:separator variant="subtle" />
        
        <flux:switch 
            wire:model.live="social" 
            label="Social emails" 
            description="Receive emails for friend requests, follows, and more." 
        />
        
        <flux:separator variant="subtle" />
        
        <flux:switch 
            wire:model.live="security" 
            label="Security emails" 
            description="Receive emails about your account activity and security." 
        />
    </div>
</flux:fieldset>
```

## Left Align

Left align switches for more compact layouts:

```blade
<flux:fieldset>
    <flux:legend>Email notifications</flux:legend>
    
    <div class="space-y-3">
        <flux:switch label="Communication emails" align="left" />
        <flux:switch label="Marketing emails" align="left" />
        <flux:switch label="Social emails" align="left" />
        <flux:switch label="Security emails" align="left" />
    </div>
</flux:fieldset>
```

## API Reference

### `<flux:switch>`

**Props:**
- `wire:model` - Bind to Livewire property (use `wire:model.live` for auto-saving)
- `label` - Label text (auto-wraps in `flux:field` with `flux:label`)
- `description` - Help text below switch (when `label` provided)
- `align` - Switch alignment relative to label:
  - `right` or `start` (default) - Switch on right side
  - `left` or `end` - Switch on left side
- `disabled` - Prevent user interaction (boolean)

**Data Attributes:**
- `data-flux-switch` - Root element identifier
- `data-checked` - Present when switch is in "on" state

## Guidelines

- **When to use**:
  - Auto-saving settings (with `wire:model.live`)
  - Binary feature toggles (enable/disable)
  - Standalone controls outside forms
  - Settings pages, preferences, notifications
- **When NOT to use**:
  - Within forms requiring explicit submit - use checkboxes instead
  - Multiple selections - use checkboxes
  - Confirmation required before change - use checkboxes with submit button
- **Layout patterns**:
  - **Right align** (default): Better for settings with descriptions (more readable)
  - **Left align**: Compact layouts, simple labels without descriptions
  - **Fieldset grouping**: Related switches with legend for context
  - **Separators**: Use `flux:separator variant="subtle"` between switches
- **Descriptions**: Add explanatory text for complex settings
- **Auto-saving**: Always use `wire:model.live` for immediate persistence
- **Disabled state**: Use when toggle is contextually unavailable

**Reference:** https://fluxui.dev/components/switch
