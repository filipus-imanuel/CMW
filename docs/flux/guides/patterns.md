# Flux UI Patterns

Understanding Flux's design patterns makes the framework more intuitive and predictable.

## Props vs Attributes

**Props** are Flux-provided properties (e.g., `variant`), while **attributes** are forwarded to underlying HTML elements:

```blade
<!-- Props are processed by Flux, attributes forwarded to HTML -->
<flux:button variant="primary" x-on:change.prevent="...">
    Submit
</flux:button>

<!-- Renders as: -->
<button type="button" class="bg-zinc-900 ..." x-on:change.prevent="...">
```

## Class Merging

Custom Tailwind classes automatically merge with Flux's internal classes:

```blade
<flux:button class="w-full">Submit</flux:button>

<!-- Renders as: -->
<button type="button" class="w-full border border-zinc-200 ...">
```

### Resolving Class Conflicts

Use Tailwind's `!important` modifier to override Flux's internal classes:

```blade
<!-- Without ! modifier - conflict occurs -->
<flux:button class="bg-zinc-800 hover:bg-zinc-700">

<!-- With ! modifier - your classes take precedence -->
<flux:button class="bg-zinc-800! hover:bg-zinc-700!">
```

**Alternatives to `!` modifier:**
- Publish component and add custom variant
- Globally customize via data attributes
- Create new component for unique case

## Split Attribute Forwarding

Complex components split attributes between elements:

```blade
<flux:input class="w-full" autofocus>

<!-- Renders as: -->
<div class="w-full ...">
    <input type="text" class="..." autofocus>
</div>
```

Styling attributes (`class`) go to wrapper, behavioral ones (`autofocus`) go to input.

## Common Props

### Variant
Alternate visual styles:

```blade
<flux:button variant="primary" />
<flux:input variant="filled" />
<flux:modal variant="flyout" />
<flux:badge variant="solid" />
<flux:tabs variant="segmented" />
```

### Icon
Pass [Heroicons](https://heroicons.com/) names directly:

```blade
<flux:button icon="magnifying-glass" />
<flux:input icon="magnifying-glass" />
<flux:tab icon="cog-6-tooth" />
<flux:badge icon="user" />

<!-- Trailing icon -->
<flux:button icon:trailing="chevron-down" />
<flux:input icon:trailing="credit-card" />
```

### Size
Size variations:

```blade
<!-- Smaller -->
<flux:button size="sm" />
<flux:input size="sm" />

<!-- Larger -->
<flux:heading size="lg" />
<flux:badge size="lg" />
```

### Keyboard Hints
Add keyboard shortcut decorations:

```blade
<flux:button kbd="⌘S" />
<flux:tooltip kbd="D" />
<flux:input kbd="⌘K" />
<flux:menu.item kbd="⌘E" />
```

### Inset
Add negative margin to align inline with text:

```blade
<flux:badge inset="top bottom">
<flux:button variant="ghost" inset="left">
```

## Prop Forwarding

Nested props use prefix syntax:

```blade
<!-- Simple prop -->
<flux:button icon="bell" />

<!-- Nested prop with prefix -->
<flux:button icon="bell" icon:variant="solid" />
```

## Opt-out Props

Force prop to `false` using dynamic syntax:

```blade
<flux:navbar.item :current="false">
```

## Shorthand Props

Common patterns have shorthand syntax:

```blade
<!-- Longform -->
<flux:field>
    <flux:label>Email</flux:label>
    <flux:input wire:model="email" type="email" />
    <flux:error name="email" />
</flux:field>

<!-- Shorthand -->
<flux:input type="email" wire:model="email" label="Email" />
```

```blade
<!-- Tooltip longform -->
<flux:tooltip content="Settings">
    <flux:button icon="cog-6-tooth" />
</flux:tooltip>

<!-- Tooltip shorthand -->
<flux:button icon="cog-6-tooth" tooltip="Settings" />
```

## Data Binding

Use `wire:model` directly on Flux components:

```blade
<flux:input wire:model="email" />
<flux:checkbox wire:model="terms" />
<flux:switch wire:model.live="enabled" />
<flux:textarea wire:model="content" />
<flux:select wire:model="state" />

<!-- Group bindings -->
<flux:checkbox.group wire:model="notifications">
<flux:radio.group wire:model="payment">
<flux:tabs wire:model="activeTab">
```

Works with Alpine.js too: `x-model` or `x-on:change`

## Component Groups

### Standalone with `.group` wrapper
Components can be used alone or grouped:

```blade
<flux:button.group>
    <flux:button />
</flux:button.group>

<flux:input.group>
    <flux:input />
</flux:input.group>

<flux:checkbox.group>
    <flux:checkbox />
</flux:checkbox.group>
```

### Parent with `.item` children
Components that only work grouped:

```blade
<flux:accordion>
    <flux:accordion.item />
</flux:accordion>

<flux:menu>
    <flux:menu.item />
</flux:menu>

<flux:navbar>
    <flux:navbar.item />
</flux:navbar>

<flux:breadcrumbs>
    <flux:breadcrumbs.item />
</flux:breadcrumbs>
```

## Root Components

Primitive components use bare names (no prefix):

```blade
<flux:field>
    <flux:label></flux:label>
    <flux:description></flux:description>
    <flux:error></flux:error>
</flux:field>
```

Avoids overly verbose hierarchies like `flux:field.label.badge`.

### Anomalies

Some components break patterns for better UX:

```blade
<flux:tab.group>
    <flux:tabs>
        <flux:tab>
    </flux:tabs>
    <flux:tab.panel>
</flux:tab.group>
```

## Slots

Used when composition isn't sufficient:

```blade
<!-- Simple icon prop -->
<flux:input icon:trailing="x-mark" />

<!-- Slot for custom content -->
<flux:input>
    <x-slot name="iconTrailing">
        <flux:button icon="x-mark" size="sm" variant="subtle" wire:click="clear" />
    </x-slot>
</flux:input>
```

## Blade Component Gotchas

Cannot use `@if` inside component tags - use dynamic attributes instead:

```blade
<!-- ❌ Wrong: @if inside component tag -->
<flux:input @if($disabled) disabled @endif>

<!-- ✅ Correct: Dynamic attribute syntax -->
<flux:input :disabled="$disabled">
```

**Reference:** https://fluxui.dev/docs/patterns
