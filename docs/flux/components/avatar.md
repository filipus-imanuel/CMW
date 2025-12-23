# Avatar Component (Flux Pro)

Display user images, initials, or icons as avatars.

---

## Basic Usage

```blade
<!-- Image avatar -->
<flux:avatar src="https://example.com/user.jpg" />

<!-- Initials from name -->
<flux:avatar name="John Doe" />

<!-- Custom initials -->
<flux:avatar initials="JD" />

<!-- Icon avatar -->
<flux:avatar icon="user" />
```

---

## Sizes

Available sizes: `xs` (24px), `sm` (32px), default (40px), `lg` (48px), `xl` (64px)

```blade
<flux:avatar size="xs" src="..." />
<flux:avatar size="sm" src="..." />
<flux:avatar src="..." />           <!-- Default: 40px -->
<flux:avatar size="lg" src="..." />
<flux:avatar size="xl" src="..." />
```

---

## Colors (for Initials/Icons)

```blade
<!-- Specific color -->
<flux:avatar name="John Doe" color="blue" />

<!-- Auto-generate color from name -->
<flux:avatar name="John Doe" color="auto" />

<!-- Consistent color based on user ID -->
<flux:avatar name="John Doe" color="auto" color:seed="{{ $user->id }}" />
```

**Available colors**: red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose

---

## Shape

```blade
<!-- Circular avatar -->
<flux:avatar circle src="..." />
```

---

## Tooltip

```blade
<!-- Custom tooltip -->
<flux:avatar tooltip="John Doe" src="..." />

<!-- Auto tooltip from name -->
<flux:avatar tooltip name="John Doe" src="..." />
```

---

## Badge

```blade
<!-- Simple dot badge -->
<flux:avatar badge badge:color="green" src="..." />

<!-- Badge with content -->
<flux:avatar badge="25" src="..." />

<!-- Emoji badge -->
<flux:avatar circle badge="👍" badge:circle src="..." />

<!-- Badge positioning -->
<flux:avatar badge badge:position="top right" badge:variant="outline" src="..." />
```

**Badge options**:
- `badge:color` - Same colors as avatar
- `badge:circle` - Make badge circular
- `badge:position` - `top left`, `top right`, `bottom left`, `bottom right` (default)
- `badge:variant` - `solid` (default), `outline`

---

## Avatar Groups

```blade
<flux:avatar.group>
    <flux:avatar src="https://example.com/user1.jpg" />
    <flux:avatar src="https://example.com/user2.jpg" />
    <flux:avatar src="https://example.com/user3.jpg" />
    <flux:avatar>3+</flux:avatar>
</flux:avatar.group>

<!-- Custom ring colors for different backgrounds -->
<flux:avatar.group class="*:ring-zinc-100 dark:*:ring-zinc-800">
    <flux:avatar circle src="..." />
    <flux:avatar circle src="..." />
</flux:avatar.group>
```

---

## Interactive Avatars

```blade
<!-- As button -->
<flux:avatar as="button" src="..." />

<!-- As link -->
<flux:avatar href="https://example.com/profile" src="..." />
```

---

## Common Patterns

### Table with Avatars
```blade
<flux:table>
    <flux:table.rows>
        <flux:table.row>
            <flux:table.cell>
                <div class="flex items-center gap-4">
                    <flux:avatar circle size="lg" src="..." />
                    <div class="flex flex-col">
                        <flux:heading>John Doe</flux:heading>
                        <flux:text>john@example.com</flux:text>
                    </div>
                </div>
            </flux:table.cell>
        </flux:table.row>
    </flux:table.rows>
</flux:table>
```

### Select with Avatars
```blade
<flux:select variant="listbox" label="Assign to">
    <flux:select.option selected>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <flux:avatar circle size="xs" src="..." /> John Doe
        </div>
    </flux:select.option>
</flux:select>
```

### List with Avatars
```blade
<ul class="flex flex-col gap-3">
    <li class="flex items-center gap-2">
        <flux:avatar size="xs" src="..." />
        <flux:heading>John Doe</flux:heading>
    </li>
</ul>
```

---

## Props Reference

### flux:avatar

| Prop | Description |
|------|-------------|
| `name` | User's name to display as initials |
| `src` | Image URL |
| `initials` | Custom initials (overrides name) |
| `alt` | Alternative text (defaults to name) |
| `size` | `xs`, `sm`, default, `lg`, `xl` |
| `color` | Background color for initials/icons |
| `color:seed` | Value for consistent auto-color generation |
| `circle` | Make avatar circular |
| `icon` | Icon name to display |
| `icon:variant` | `outline`, `solid` (default) |
| `tooltip` | Tooltip text (or `true` to use name) |
| `tooltip:position` | `top`, `right`, `bottom`, `left` |
| `badge` | Badge content (string, boolean, or slot) |
| `badge:color` | Badge color |
| `badge:circle` | Make badge circular |
| `badge:position` | Badge position |
| `badge:variant` | `solid` (default), `outline` |
| `as` | `button`, `div` (default) |
| `href` | URL to link to |

### flux:avatar.group

| Prop | Description |
|------|-------------|
| `class` | CSS classes (use `*:ring-{color}` for custom ring colors) |

---

## Usage Guidelines

- **Tables**: Use `size="lg"` with `circle` for user lists
- **Compact lists**: Use `size="xs"` or `size="sm"`
- **Auto colors**: Always use `color:seed` with user ID for consistency
- **Badges**: Use for online status (`green`), notifications (count), or special indicators
- **Groups**: Stack avatars with `flux:avatar.group` for team/collaboration displays
- **Initials**: Automatically generated from name (first letter of each word)
- **Missing images**: Falls back to initials or icon gracefully

**Reference**: https://fluxui.dev/components/avatar