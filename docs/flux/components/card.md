# Card Component (Flux Pro)

A container for related content, such as forms, alerts, data lists, or grouped information.

## Basic Usage

```blade
<!-- Simple card with content -->
<flux:card>
    <flux:heading size="lg">Are you sure?</flux:heading>
    <flux:text class="mt-2 mb-4">
        Your post will be deleted permanently.<br>
        This action cannot be undone.
    </flux:text>
    <flux:button variant="danger">Delete</flux:button>
</flux:card>

<!-- Card with spacing between elements -->
<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">Log in to your account</flux:heading>
        <flux:text class="mt-2">Welcome back!</flux:text>
    </div>
    
    <div class="space-y-6">
        <flux:input label="Email" type="email" placeholder="Your email address" />
        <flux:input label="Password" type="password" placeholder="Your password" />
    </div>
    
    <div class="space-y-2">
        <flux:button variant="primary" class="w-full">Log in</flux:button>
        <flux:button variant="ghost" class="w-full">Sign up for a new account</flux:button>
    </div>
</flux:card>
```

## Size Variants

```blade
<!-- Small card for compact content (notifications, alerts, brief summaries) -->
<flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
    <flux:heading class="flex items-center gap-2">
        Latest on our blog 
        <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro" />
    </flux:heading>
    <flux:text class="mt-2">
        Stay up to date with our latest insights, tutorials, and product updates.
    </flux:text>
</flux:card>
```

## Header with Actions

```blade
<!-- Card with header actions and close button -->
<flux:card class="space-y-6">
    <div class="flex">
        <div class="flex-1">
            <flux:heading size="lg">Are you sure?</flux:heading>
            <flux:text class="mt-2">
                Your post will be deleted permanently.<br>
                This action cannot be undone.
            </flux:text>
        </div>
        <div class="-mx-2 -mt-2">
            <flux:button variant="ghost" size="sm" icon="x-mark" inset="top right bottom" />
        </div>
    </div>
    
    <div class="flex gap-4">
        <flux:spacer />
        <flux:button variant="ghost">Undo</flux:button>
        <flux:button variant="danger">Delete</flux:button>
    </div>
</flux:card>
```

## Common Patterns

**Page Layout Pattern:**
```blade
<!-- Index Page -->
<div>
    <div class="flex justify-end">
        <flux:button href="{{ route('resource.create') }}" variant="primary" wire:navigate>
            Create New Resource
        </flux:button>
    </div>
    
    <flux:card class="mt-4">
        <flux:heading size="lg">List of Resources</flux:heading>
        <livewire:resource-index-data-table />
    </flux:card>
</div>

<!-- Create/Edit Page -->
<div>
    <div class="flex justify-start">
        <flux:button href="{{ route('resource.index') }}" icon="arrow-left" variant="ghost" wire:navigate>
            Back to Resources
        </flux:button>
    </div>
    
    <flux:card class="mt-4">
        <flux:heading size="lg">Create Resource</flux:heading>
        
        <form wire:submit="save">
            <div class="space-y-4">
                <flux:spacer />
                <!-- Form fields -->
                
                <div class="flex pt-2">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">Submit</flux:button>
                </div>
            </div>
        </form>
    </flux:card>
</div>
```

## Attributes

| Attribute | Description |
|-----------|-------------|
| `size` | Card size variant. Options: `sm` (compact) |
| `class` | Additional CSS classes. Common: `space-y-6` (spacing), `mt-4` (margin), `max-w-md` (width), `p-0` (remove padding) |

## Styling Guidelines

- **Index pages**: Single card wrapping datatable with heading inside
- **Create/Edit pages**: Main card with nested cards for logical sections
- **Show pages**: Multiple cards separating different data sections
- **Spacing**: Use `mt-4` for card spacing, `space-y-4` or `space-y-6` inside forms
- **Headings**: Use `size="lg"` inside cards (not `size="xl"`)

**Reference:** https://fluxui.dev/components/card
