# Flux UI Theming

Flux looks great out of the box, but every project has its own identity. Customize colors using CSS variables or choose from hand-picked color schemes.

**Interactive Theme Builder:** https://fluxui.dev/themes

## Base Color

Two core colors in Flux: **base color** (majority content) and **accent color** (primary actions).

**Base color** used for: text, backgrounds, borders, etc.

Default: `zinc` (hard-coded in source)

### Change Base Color

Redefine `zinc` to another gray shade in your CSS:

```css
/* resources/css/app.css */

/* Re-assign Flux's gray to slate */
@theme {
  --color-zinc-50: var(--color-slate-50);
  --color-zinc-100: var(--color-slate-100);
  --color-zinc-200: var(--color-slate-200);
  --color-zinc-300: var(--color-slate-300);
  --color-zinc-400: var(--color-slate-400);
  --color-zinc-500: var(--color-slate-500);
  --color-zinc-600: var(--color-slate-600);
  --color-zinc-700: var(--color-slate-700);
  --color-zinc-800: var(--color-slate-800);
  --color-zinc-900: var(--color-slate-900);
  --color-zinc-950: var(--color-slate-950);
}
```

Now use `slate` in your utilities:

```blade
<flux:text class="text-slate-800 dark:text-white">...</flux:text>
```

## Accent Color

Flux uses CSS variables for accent colors. Change to any color you like.

**Recommendation:** Use [interactive theme builder](https://fluxui.dev/themes) with pre-selected colors.

### Custom Accent Color

Define three hues for light and dark modes:

```css
/* resources/css/app.css */

@theme {
    --color-accent: var(--color-red-500);
    --color-accent-content: var(--color-red-600);
    --color-accent-foreground: var(--color-white);
}

@layer theme {
    .dark {
        --color-accent: var(--color-red-500);
        --color-accent-content: var(--color-red-400);
        --color-accent-foreground: var(--color-white);
    }
}
```

### Accent Color Variables

| Variable | Description |
|----------|-------------|
| `--color-accent` | Main accent color for primary button backgrounds |
| `--color-accent-content` | Darker hue for text content (better readability) |
| `--color-accent-foreground` | Text color on accent colored backgrounds |

### Using Accent Colors

```blade
<!-- Verbose Tailwind syntax -->
<button class="bg-[var(--color-accent)] text-[var(--color-accent-foreground)]">

<!-- Ergonomic utility classes -->
<button class="bg-accent text-accent-foreground">
```

## Accent Props

Certain elements (tabs, links) use accent color by default. Opt out with `:accent="false"`:

```blade
<!-- Link -->
<flux:link :accent="false">Profile</flux:link>

<!-- Tabs -->
<flux:tabs>
    <flux:tab :accent="false">Profile</flux:tab>
    <flux:tab :accent="false">Account</flux:tab>
    <flux:tab :accent="false">Billing</flux:tab>
</flux:tabs>

<!-- Navbar -->
<flux:navbar>
    <flux:navbar.item :accent="false">Profile</flux:navbar.item>
    <flux:navbar.item :accent="false">Account</flux:navbar.item>
    <flux:navbar.item :accent="false">Billing</flux:navbar.item>
</flux:navbar>

<!-- Navlist -->
<flux:navlist>
    <flux:navlist.item :accent="false">Profile</flux:navlist.item>
    <flux:navlist.item :accent="false">Account</flux:navlist.item>
    <flux:navlist.item :accent="false">Billing</flux:navlist.item>
</flux:navlist>
```

## Quick Summary

1. **Base Color**: Change by redefining `zinc` CSS variables to another gray shade
2. **Accent Color**: Define three hues (accent, accent-content, accent-foreground) for light/dark modes
3. **Use Theme Builder**: Easiest way to create custom themes with pre-selected colors
4. **Opt-out Accent**: Use `:accent="false"` on components that default to accent color

**Reference:** https://fluxui.dev/docs/theming
