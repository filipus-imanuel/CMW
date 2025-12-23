# Flux Tabs Component

## Basic Structure
```blade
<flux:tab.group>
    <flux:tabs wire:model="tab">
        <flux:tab name="profile">Profile</flux:tab>
        <flux:tab name="account">Account</flux:tab>
        <flux:tab name="billing">Billing</flux:tab>
    </flux:tabs>

    <flux:tab.panel name="profile">
        <!-- Profile content -->
    </flux:tab.panel>
    
    <flux:tab.panel name="account">
        <!-- Account content -->
    </flux:tab.panel>
    
    <flux:tab.panel name="billing">
        <!-- Billing content -->
    </flux:tab.panel>
</flux:tab.group>
```

## Tabs with Icons
```blade
<flux:tab.group>
    <flux:tabs>
        <flux:tab name="profile" icon="user">Profile</flux:tab>
        <flux:tab name="account" icon="cog-6-tooth">Account</flux:tab>
        <flux:tab name="billing" icon="banknotes">Billing</flux:tab>
    </flux:tabs>
    <!-- panels -->
</flux:tab.group>
```

## Padded Edges
```blade
<flux:tabs class="px-4">
    <flux:tab name="profile">Profile</flux:tab>
    <flux:tab name="account">Account</flux:tab>
    <flux:tab name="billing">Billing</flux:tab>
</flux:tabs>
```

## Scrollable Tabs
```blade
<!-- Basic scrollable -->
<flux:tabs scrollable>
    <flux:tab name="profile">Profile</flux:tab>
    <flux:tab name="account">Account</flux:tab>
    <!-- many more tabs -->
</flux:tabs>

<!-- With fade effect -->
<flux:tabs scrollable scrollable:fade>
    <!-- tabs -->
</flux:tabs>

<!-- Hide scrollbar (also hides on desktop) -->
<flux:tabs scrollable scrollable:scrollbar="hide">
    <!-- tabs -->
</flux:tabs>
```

## Segmented Tabs
```blade
<!-- Default segmented -->
<flux:tabs variant="segmented">
    <flux:tab>List</flux:tab>
    <flux:tab>Board</flux:tab>
    <flux:tab>Timeline</flux:tab>
</flux:tabs>

<!-- Segmented with icons -->
<flux:tabs variant="segmented">
    <flux:tab icon="list-bullet">List</flux:tab>
    <flux:tab icon="squares-2x2">Board</flux:tab>
    <flux:tab icon="calendar-days">Timeline</flux:tab>
</flux:tabs>

<!-- Small segmented -->
<flux:tabs variant="segmented" size="sm">
    <flux:tab>Demo</flux:tab>
    <flux:tab>Code</flux:tab>
</flux:tabs>
```

## Pill Tabs
```blade
<flux:tabs variant="pills">
    <flux:tab>List</flux:tab>
    <flux:tab>Board</flux:tab>
    <flux:tab>Timeline</flux:tab>
</flux:tabs>
```

## Dynamic Tabs
```blade
<flux:tab.group>
    <flux:tabs>
        @foreach($tabs as $id => $tab)
            <flux:tab :name="$id">{{ $tab }}</flux:tab>
        @endforeach
        
        <flux:tab icon="plus" wire:click="addTab" action>Add tab</flux:tab>
    </flux:tabs>

    @foreach($tabs as $id => $tab)
        <flux:tab.panel :name="$id">
            <!-- panel content -->
        </flux:tab.panel>
    @endforeach
</flux:tab.group>
```

```php
// Livewire component
public array $tabs = [
    'tab-1' => 'Tab #1',
    'tab-2' => 'Tab #2',
];

public function addTab(): void
{
    $id = 'tab-' . str()->random();
    $this->tabs[$id] = 'Tab #' . count($this->tabs) + 1;
}
```

## Component Reference

### `<flux:tab.group>`
Container for tabs and their associated panels.

### `<flux:tabs>`
**Props:**
- `wire:model` - Binds active tab to Livewire property
- `variant` - Visual style: `default`, `segmented`, `pills`
- `size` - Size: `base` (default), `sm` (for segmented variant)
- `scrollable` - Enables horizontal scrolling
- `scrollable:scrollbar` - Controls scrollbar visibility: `hide`
- `scrollable:fade` - Adds fade effect to trailing edge

### `<flux:tab>`
**Props:**
- `name` - Unique identifier matching associated panel
- `icon` - Icon name to display at start
- `icon:trailing` - Icon name to display at end
- `icon:variant` - Icon variant: `outline`, `solid`, `mini`, `micro`
- `action` - Converts tab to action button (for "Add tab" functionality)
- `accent` - Applies accent color styling
- `size` - Size: `base`, `sm` (only for segmented variant)
- `disabled` - Disables the tab

**Attributes:**
- `data-selected` - Applied when tab is active

### `<flux:tab.panel>`
**Props:**
- `name` - Unique identifier matching associated tab

## Usage Guidelines
- Use tabs for organizing content within a single page/container
- For full-page navigation, use navbar component instead
- Match `name` prop between tab and panel components
- Segmented/pill variants ideal for constrained width containers
- Enable scrollable for mobile responsiveness with many tabs

**Reference:** https://fluxui.dev/components/tabs
