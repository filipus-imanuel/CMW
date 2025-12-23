# Command Palette Component

A searchable list of commands for quick access to frequently used actions.

## Basic Usage

```blade
<flux:command>
    <flux:command.input placeholder="Search..." />
    <flux:command.items>
        <flux:command.item wire:click="..." icon="user-plus" kbd="⌘A">Assign to…</flux:command.item>
        <flux:command.item wire:click="..." icon="document-plus">Create new file</flux:command.item>
        <flux:command.item wire:click="..." icon="folder-plus" kbd="⌘⇧N">Create new project</flux:command.item>
        <flux:command.item wire:click="..." icon="book-open">Documentation</flux:command.item>
        <flux:command.item wire:click="..." icon="newspaper">Changelog</flux:command.item>
        <flux:command.item wire:click="..." icon="cog-6-tooth" kbd="⌘,">Settings</flux:command.item>
    </flux:command.items>
</flux:command>
```

## As a Modal

Open command palette as a modal with keyboard shortcut:

```blade
<flux:modal.trigger name="search" shortcut="cmd.k">
    <flux:input as="button" placeholder="Search..." icon="magnifying-glass" kbd="⌘K" />
</flux:modal.trigger>

<flux:modal name="search" variant="bare" class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden">
    <flux:command class="border-none shadow-lg inline-flex flex-col max-h-[76vh]">
        <flux:command.input placeholder="Search..." closable />
        <flux:command.items>
            <flux:command.item icon="user-plus" kbd="⌘A">Assign to…</flux:command.item>
            <flux:command.item icon="document-plus">Create new file</flux:command.item>
            <flux:command.item icon="folder-plus" kbd="⌘⇧N">Create new project</flux:command.item>
            <flux:command.item icon="book-open">Documentation</flux:command.item>
            <flux:command.item icon="newspaper">Changelog</flux:command.item>
            <flux:command.item icon="cog-6-tooth" kbd="⌘,">Settings</flux:command.item>
        </flux:command.items>
    </flux:command>
</flux:modal>
```

## Properties

### flux:command

Root component that wraps input and items. No props available.

### flux:command.input

| Property | Type | Description |
|----------|------|-------------|
| `placeholder` | string | Placeholder text when input is empty |
| `icon` | string | Icon displayed at start of input (default: `magnifying-glass`) |
| `clearable` | bool | Show clear button when input has content |
| `closable` | bool | Show close button to dismiss command palette |

### flux:command.items

Container for command items. No props available.

### flux:command.item

| Property | Type | Description |
|----------|------|-------------|
| `icon` | string | Icon displayed at start of item |
| `icon:variant` | string | Icon style: `outline` (default), `solid`, `mini`, `micro` |
| `kbd` | string | Keyboard shortcut hint (e.g., `⌘K`, `⌘⇧N`) |
| `wire:click` | string | Livewire action to execute when clicked |

## Guidelines

- Use modal variant for global command palette (typically triggered with `⌘K`)
- Add `kbd` attribute to show keyboard shortcuts
- Use `closable` on input when in modal to allow dismissal
- Icons help users quickly identify actions
- Combine with `flux:modal.trigger` with `shortcut` attribute for keyboard access
- Common modal classes: `max-w-[30rem]`, `my-[12vh]`, `max-h-[76vh]`

## Common Keyboard Shortcuts

- `⌘K` - Open command palette (macOS)
- `⌘A` - Assign action
- `⌘⇧N` - Create new
- `⌘,` - Settings
- `Ctrl+K` - Windows equivalent

**Reference**: https://fluxui.dev/components/command
