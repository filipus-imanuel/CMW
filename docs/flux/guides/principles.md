# Flux UI Principles

Flux is a system of UI components guided by design principles that create a cleaner, more creative, and intuitive app-building experience.

## Simplicity

Above all else, simplicity drives Flux's syntax, implementation, and visuals.

```blade
<!-- ✅ Simple Flux syntax -->
<flux:input wire:model="email" label="Email" />

<!-- ❌ Overly complex alternative -->
<flux:form.field>
    <flux:form.field.label>Email</flux:form.field.label>
    <div>
        <flux:form.field.text-input wire:model="email" />
    </div>
    @error('email')
        <p class="mt-2 text-red-500 dark:text-red-400 text-xs">{{ $message }}</p>
    @enderror
</flux:form.field>
```

## Complexity

Simplicity requires trade-offs. Flux provides composable alternatives when customization is needed.

```blade
<!-- Simple shorthand -->
<flux:input wire:model="email" label="Email" />

<!-- Composable alternative for customization -->
<flux:field>
    <flux:label>Email</flux:label>
    <flux:input wire:model="email" />
    <flux:error name="email" />
</flux:field>
```

Best of both worlds: succinct syntax for common cases, customization when needed.

## Friendliness

Flux uses familiar, friendly names instead of overly technical ones:

- "Form inputs" not "form controls"
- "Accordion" not "disclosure"
- "Input" not "text-input" or "form-control"

Common developer terminology over technical UI pattern names.

## Composition

After simplicity, composability is the highest value. Mix and match core components to create robust compositions.

```blade
<!-- Simple button -->
<flux:button>Options</flux:button>

<!-- Compose into dropdown -->
<flux:dropdown>
    <flux:button>Options</flux:button>
    <flux:navmenu>
        <!-- ... -->
    </flux:navmenu>
</flux:dropdown>

<!-- Swap navmenu for system menu -->
<flux:dropdown>
    <flux:button>Options</flux:button>
    <flux:menu>
        <!-- ... -->
    </flux:menu>
</flux:dropdown>

<!-- Reuse menu in context menu -->
<flux:context>
    <flux:button>Options</flux:button>
    <flux:menu>
        <!-- ... -->
    </flux:menu>
</flux:context>
```

Independent components combine into new, more powerful ones.

## Consistency

Inconsistent naming leads to confusion. Flux uses repeated syntax patterns throughout.

Example: "heading" consistently used across components instead of mixing "title", "name", etc.

```blade
<flux:heading>...</flux:heading>
<flux:menu.submenu heading="...">
<flux:accordion.heading>...</flux:accordion.heading>
```

Memorize patterns once, apply everywhere.

## Brevity

Flux aims for brevity without sacrificing other principles:

- Avoid compound words requiring hyphens
- Avoid deeply nested (dot-separated) names
- Single-word names when possible

```blade
<!-- ✅ Brief Flux syntax -->
<flux:dropdown>
    <flux:button>Options</flux:button>
    <flux:menu>
        <!-- ... -->
    </flux:menu>
</flux:dropdown>

<!-- ❌ Verbose alternative -->
<flux:dropdown-menu>
    <flux:dropdown-menu.button>Options</flux:dropdown-menu.button>
    <flux:dropdown-menu.items>
        <!-- ... -->
    </flux:dropdown-menu.items>
</flux:dropdown-menu>
```

## Use the Browser

Leverage modern browser features for reliable behavior without extra JavaScript.

**Popover API** for dropdowns:
```html
<div popover>
    <!-- ... -->
</div>
```
[MDN Popover API](https://developer.mozilla.org/en-US/docs/Web/API/Popover_API)

**Dialog element** for modals:
```html
<dialog>
    <!-- ... -->
</dialog>
```
[MDN Dialog Element](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog)

Native features provide consistent behavior, focus management, accessibility, and keyboard navigation out of the box.

## Use CSS

Leverage modern CSS features (`:has()`, `:not()`, `:where()`) instead of JavaScript when possible.

Example: Search icon color change on focus without JavaScript:

```blade
<flux:command.input placeholder="Search..." />
```

Uses CSS `:has()` selector:
```css
[&:has(+input:focus)]:text-zinc-800
```

Matches elements with focused sibling input, changes color without JavaScript.

## We Style, You Space

**Flux provides styling (padding), you provide spacing (margins).**

```blade
<form wire:submit="createAccount">
    <div class="mb-6">
        <flux:heading>Create an account</flux:heading>
        <flux:text class="mt-2">We're excited to have you on board.</flux:text>
    </div>

    <flux:input class="mb-6" label="Email" wire:model="email" />

    <div class="mb-6 flex *:w-1/2 gap-4">
        <flux:input label="Password" wire:model="password" />
        <flux:input label="Confirm password" wire:model="password_confirmation" />
    </div>

    <flux:button type="submit" variant="primary">Create account</flux:button>
</form>
```

**Why?** Spacing is contextual, styling is less so. Baking in spacing would require constant overrides or risk disjointed layouts. Flexibility worth the slight extra effort.

## Key Takeaways

1. **Simplicity first** - Clean, minimal syntax
2. **Composable** - Mix components to create new ones
3. **Consistent** - Repeated patterns throughout
4. **Brief** - Short names, avoid nesting
5. **Modern** - Leverage browser and CSS features
6. **Flexible spacing** - You control layout context

**Reference:** https://fluxui.dev/docs/principles
