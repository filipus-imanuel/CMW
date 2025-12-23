# Skeleton Component (Flux Pro)

Create placeholder content while loading data. Provides visual feedback that content is being loaded, improving perceived performance.

---

## Basic Usage

```blade
<flux:skeleton.group animate="shimmer" class="flex items-center gap-4">
    <flux:skeleton class="size-10 rounded-full" />
    <div class="flex-1">
        <flux:skeleton.line />
        <flux:skeleton.line class="w-1/2" />
    </div>
</flux:skeleton.group>
```

**Features:**
- Placeholder boxes and text lines
- Shimmer and pulse animations
- Grouping for coordinated animations
- Fully customizable with Tailwind classes

---

## Line of Text

Create loading state for text content. The `flux:skeleton.line` component occupies full line-height (~20px) while rendering a slimmer bar, giving it the visual proportions of real text.

```blade
<flux:skeleton.group animate="shimmer">
    <flux:skeleton.line class="mb-2 w-1/4" />
    <flux:skeleton.line />
    <flux:skeleton.line />
    <flux:skeleton.line class="w-3/4" />
</flux:skeleton.group>
```

**Use for:**
- Paragraph text placeholders
- Heading placeholders
- Multi-line content loading states
- Variable-width text simulation

---

## Animation

Add animation to enhance loading feedback.

```blade
<!-- No animation -->
<flux:skeleton />

<!-- Shimmer animation -->
<flux:skeleton animate="shimmer" />

<!-- Pulse animation -->
<flux:skeleton animate="pulse" />
```

**Animation options:**
- `shimmer` - Smooth left-to-right shimmer effect (recommended)
- `pulse` - Subtle opacity pulsing
- None (default) - Static placeholder

**Best practices:**
- Use `shimmer` for most loading states
- Use `pulse` for subtle, less distracting feedback
- Omit animation for very short load times (<500ms)

---

## Examples

### Table Loading State

```blade
<flux:skeleton.group animate="shimmer">
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
        </flux:table.columns>
        
        <flux:table.rows>
            @foreach(range(1, 5) as $order)
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:skeleton class="rounded-full size-5" />
                            <div class="flex-1">
                                <flux:skeleton.line style="width: {{ rand(50, 100) }}%" />
                            </div>
                        </div>
                    </flux:table.cell>
                    
                    <flux:table.cell>
                        <flux:skeleton.line />
                    </flux:table.cell>
                    
                    <flux:table.cell>
                        <flux:skeleton.line />
                    </flux:table.cell>
                    
                    <flux:table.cell>
                        <flux:skeleton.line />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</flux:skeleton.group>
```

---

### Chart Loading State

```blade
<flux:card class="dark:bg-zinc-800">
    <div class="flex flex-col gap-6">
        <div class="flex gap-12">
            <div>
                <flux:text>Today</flux:text>
                <flux:heading size="xl" class="mt-2 tabular-nums">$---</flux:heading>
                <flux:text class="mt-2 tabular-nums">-:-- PM</flux:text>
            </div>
            
            <div>
                <flux:text>Yesterday</flux:text>
                <flux:heading size="lg" class="mt-2 tabular-nums">$---</flux:heading>
            </div>
        </div>
        
        <flux:skeleton animate="shimmer" class="aspect-[4/1] size-full rounded-lg" />
    </div>
</flux:card>
```

---

### Card Loading State

```blade
<flux:skeleton.group animate="shimmer">
    <flux:card>
        <div class="flex items-start gap-4">
            <flux:skeleton class="size-12 rounded-lg" />
            
            <div class="flex-1 space-y-2">
                <flux:skeleton.line class="w-1/3" />
                <flux:skeleton.line />
                <flux:skeleton.line class="w-5/6" />
            </div>
        </div>
    </flux:card>
</flux:skeleton.group>
```

---

### User Profile Loading State

```blade
<flux:skeleton.group animate="shimmer" class="flex items-center gap-4">
    <!-- Avatar -->
    <flux:skeleton class="size-16 rounded-full" />
    
    <!-- User info -->
    <div class="flex-1">
        <flux:skeleton.line size="lg" class="w-1/4 mb-2" />
        <flux:skeleton.line class="w-1/2" />
    </div>
</flux:skeleton.group>
```

---

### List Loading State

```blade
<flux:skeleton.group animate="shimmer" class="space-y-4">
    @foreach(range(1, 5) as $item)
        <div class="flex items-center gap-3">
            <flux:skeleton class="size-10 rounded" />
            <div class="flex-1">
                <flux:skeleton.line class="w-3/4 mb-1" />
                <flux:skeleton.line class="w-1/2" />
            </div>
        </div>
    @endforeach
</flux:skeleton.group>
```

---

### Form Loading State

```blade
<flux:skeleton.group animate="shimmer" class="space-y-6">
    @foreach(range(1, 4) as $field)
        <div>
            <flux:skeleton.line class="w-1/4 mb-2" />
            <flux:skeleton class="h-10 w-full rounded-lg" />
        </div>
    @endforeach
    
    <flux:skeleton class="h-10 w-32 rounded-lg" />
</flux:skeleton.group>
```

---

## API Reference

### flux:skeleton

Basic skeleton box element.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `animate` | enum | - | Animation style. Options: `shimmer`, `pulse` |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Skeleton elements (lines, boxes, circles) |

**CSS Variables:**
| Variable | Description |
|----------|-------------|
| `--flux-shimmer-color` | Background color for shimmer animation. Defaults to white (light mode) or `var(--color-zinc-900)` (dark mode). Set to match page background to prevent shimmer showing through gaps |

**Attributes:**
| Attribute | Description |
|-----------|-------------|
| `data-flux-skeleton` | Applied to root element for styling |

---

### flux:skeleton.line

Text line placeholder with proper line-height spacing.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `size` | enum | `base` | Line size. Options: `base`, `lg` |
| `animate` | enum | - | Animation style. Options: `shimmer`, `pulse`. Inherits from parent `flux:skeleton.group` |

**Attributes:**
| Attribute | Description |
|-----------|-------------|
| `data-flux-skeleton-line` | Applied to root element for styling |

---

### flux:skeleton.group

Container that applies animation to all child skeleton elements.

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `animate` | enum | - | Animation style for all children. Options: `shimmer`, `pulse`. Inherited by child `flux:skeleton` and `flux:skeleton.line` |

**Attributes:**
| Attribute | Description |
|-----------|-------------|
| `data-flux-skeleton-group` | Applied to root element for styling |

---

## Usage Guidelines

**When to use Skeleton:**
- Loading data from API/database
- Page transitions
- Lazy-loaded content
- Progressive data loading
- Initial page render while fetching

**When NOT to use:**
- Very fast operations (<200ms)
- Form submissions (use button loading state)
- Background refreshes (use subtle indicators)
- Error states (use callouts instead)

**Best Practices:**
- Match skeleton layout to actual content structure
- Use `flux:skeleton.group` to coordinate animations
- Add `animate="shimmer"` for engaging feedback
- Use rounded corners matching final content
- Randomize widths slightly for realistic appearance
- Show actual headers/labels instead of skeleton lines
- Keep skeleton simple - don't over-engineer
- Test dark mode appearance

**Performance tips:**
- Render skeletons server-side when possible
- Use `wire:loading.class` to toggle between content and skeleton
- Avoid complex skeleton layouts that are slow to render

---

## Common Patterns

**Basic Content Block:**
```blade
<flux:skeleton.group animate="shimmer">
    <flux:skeleton.line size="lg" class="w-1/3 mb-4" />
    <flux:skeleton.line class="mb-2" />
    <flux:skeleton.line class="mb-2" />
    <flux:skeleton.line class="w-4/5" />
</flux:skeleton.group>
```

**Media Item with Text:**
```blade
<flux:skeleton.group animate="shimmer" class="flex gap-4">
    <flux:skeleton class="size-20 rounded-lg" />
    <div class="flex-1">
        <flux:skeleton.line size="lg" class="w-2/3 mb-2" />
        <flux:skeleton.line class="w-full mb-2" />
        <flux:skeleton.line class="w-3/4" />
    </div>
</flux:skeleton.group>
```

**Grid Layout:**
```blade
<flux:skeleton.group animate="shimmer" class="grid grid-cols-3 gap-4">
    @foreach(range(1, 6) as $item)
        <div>
            <flux:skeleton class="aspect-video w-full rounded-lg mb-2" />
            <flux:skeleton.line class="w-3/4 mb-1" />
            <flux:skeleton.line class="w-1/2" />
        </div>
    @endforeach
</flux:skeleton.group>
```

---

## Livewire Integration

**Toggle between skeleton and content:**
```blade
<div>
    <div wire:loading>
        <flux:skeleton.group animate="shimmer">
            <flux:skeleton.line size="lg" class="w-1/3 mb-4" />
            <flux:skeleton.line />
            <flux:skeleton.line />
            <flux:skeleton.line class="w-3/4" />
        </flux:skeleton.group>
    </div>
    
    <div wire:loading.remove>
        <h2>{{ $title }}</h2>
        <p>{{ $content }}</p>
    </div>
</div>
```

**Conditional skeleton with wire:loading.class:**
```blade
<div wire:loading.class="hidden">
    <!-- Actual content -->
    <h2>{{ $title }}</h2>
    <p>{{ $content }}</p>
</div>

<div wire:loading.class.remove="hidden" class="hidden">
    <flux:skeleton.group animate="shimmer">
        <flux:skeleton.line size="lg" class="w-1/3 mb-4" />
        <flux:skeleton.line />
        <flux:skeleton.line />
    </flux:skeleton.group>
</div>
```

**Table with loading state:**
```blade
@if($loading)
    <!-- Skeleton table -->
    <flux:skeleton.group animate="shimmer">
        <flux:table>
            <!-- Skeleton rows -->
        </flux:table>
    </flux:skeleton.group>
@else
    <!-- Actual table -->
    <flux:table>
        @foreach($items as $item)
            <!-- Actual rows -->
        @endforeach
    </flux:table>
@endif
```

---

## Dark Mode Support

Skeleton automatically adapts to dark mode. The shimmer color defaults to `--color-zinc-900` in dark mode.

**Custom shimmer color:**
```blade
<flux:skeleton.group 
    animate="shimmer" 
    style="--flux-shimmer-color: rgb(24 24 27)">
    <flux:skeleton.line />
    <flux:skeleton.line />
</flux:skeleton.group>
```

---

**Reference:** https://fluxui.dev/components/skeleton
