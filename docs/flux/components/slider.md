# Slider Component (Flux Pro)

Select a value or range using a horizontal slider control. Perfect for adjusting settings, filtering by numeric ranges, and selecting values within a defined range.

---

## Basic Usage

```blade
<flux:slider wire:model="amount" />
```

```php
// Livewire Component
public int $amount = 50;
```

**Features:**
- Single value selection
- Range selection with two thumbs
- Keyboard navigation (arrow keys)
- Min/max/step configuration
- Visual step markers
- Customizable styling

---

## Min/Max/Step

Configure the slider's value constraints and increment size.

```blade
<flux:slider min="0" max="100" step="10" />
```

**Props:**
- `min` - Minimum value (default: 0)
- `max` - Maximum value (default: 100)
- `step` - Increment size (default: 1)

**Example configurations:**
```blade
<!-- Percentage slider -->
<flux:slider min="0" max="100" step="1" />

<!-- Price slider in $10 increments -->
<flux:slider min="0" max="1000" step="10" />

<!-- Temperature in 0.5° increments -->
<flux:slider min="-10" max="40" step="0.5" />
```

---

## Displaying Value

Display the current value using `wire:text` directive.

```blade
<flux:field>
    <flux:label>
        Corner radius
        <x-slot name="trailing">
            <span wire:text="amount" class="tabular-nums"></span>
        </x-slot>
    </flux:label>
    <flux:slider wire:model="amount" />
</flux:field>
```

**Benefits:**
- Real-time value display
- No JavaScript needed (Livewire handles it)
- Use `tabular-nums` class for consistent width

---

## With Input

Combine slider with numeric input for precise value entry.

```blade
<flux:field>
    <flux:label>Corner radius</flux:label>
    <div class="flex items-center gap-4 -mt-2">
        <flux:slider wire:model="amount" />
        <flux:input wire:model="amount" type="number" size="sm" class="max-w-18" />
    </div>
</flux:field>
```

**Use cases:**
- Precise value entry needed
- Large value ranges
- Accessibility (some users prefer input fields)

---

## Big Steps

Define larger step size when holding Shift key while using arrow keys.

```blade
<flux:slider step="1" big-step="10" />
```

**Keyboard navigation:**
- `←/→` - Move by `step` amount (1)
- `Shift + ←/→` - Move by `big-step` amount (10)
- `Home` - Jump to minimum
- `End` - Jump to maximum

---

## Step Marks

Display tick marks below the slider to visualize steps.

```blade
<flux:slider min="1" max="5">
    @foreach(range(1, 5) as $i)
        <flux:slider.tick :value="$i" />
    @endforeach
</flux:slider>
```

**Visual feedback:**
- Shows available step positions
- Helps users understand granularity
- Works with any step configuration

---

## Numbered Steps

Display numbers below the slider for labeled steps.

```blade
<flux:slider min="1" max="5">
    @foreach(range(1, 5) as $i)
        <flux:slider.tick :value="$i">{{ $i }}</flux:slider.tick>
    @endforeach
</flux:slider>
```

**Common patterns:**
```blade
<!-- Rating slider -->
<flux:slider min="1" max="5">
    @foreach(range(1, 5) as $rating)
        <flux:slider.tick :value="$rating">{{ $rating }}</flux:slider.tick>
    @endforeach
</flux:slider>

<!-- Quantity selector -->
<flux:slider min="0" max="100" step="10">
    @foreach(range(0, 100, 10) as $qty)
        <flux:slider.tick :value="$qty">{{ $qty }}</flux:slider.tick>
    @endforeach
</flux:slider>
```

---

## Custom Steps

Display custom labels for specified steps.

```blade
<flux:slider min="1" max="5">
    <flux:slider.tick value="1">Low</flux:slider.tick>
    <flux:slider.tick value="3">Mid</flux:slider.tick>
    <flux:slider.tick value="5">High</flux:slider.tick>
</flux:slider>
```

**Use cases:**
```blade
<!-- Priority levels -->
<flux:slider min="1" max="4">
    <flux:slider.tick value="1">Low</flux:slider.tick>
    <flux:slider.tick value="2">Medium</flux:slider.tick>
    <flux:slider.tick value="3">High</flux:slider.tick>
    <flux:slider.tick value="4">Critical</flux:slider.tick>
</flux:slider>

<!-- Size selection -->
<flux:slider min="1" max="4">
    <flux:slider.tick value="1">XS</flux:slider.tick>
    <flux:slider.tick value="2">S</flux:slider.tick>
    <flux:slider.tick value="3">M</flux:slider.tick>
    <flux:slider.tick value="4">L</flux:slider.tick>
</flux:slider>

<!-- Speed settings -->
<flux:slider min="0" max="3">
    <flux:slider.tick value="0">Off</flux:slider.tick>
    <flux:slider.tick value="1">Slow</flux:slider.tick>
    <flux:slider.tick value="2">Normal</flux:slider.tick>
    <flux:slider.tick value="3">Fast</flux:slider.tick>
</flux:slider>
```

---

## Range Slider

Select a range of values using two thumbs.

```blade
<flux:slider range />
```

**Basic range:**
```blade
<!-- Set initial range with value prop -->
<flux:slider range value="20,80" />

<!-- Bind to Livewire property -->
<flux:slider range wire:model="range" />
```

```php
// Livewire Component
public array $range = [20, 80];
```

---

### Min Steps Between

Set minimum distance between thumbs (in number of steps).

```blade
<flux:slider range step="1" min-steps-between="10" />
```

**Prevents:**
- Thumbs getting too close
- Invalid ranges (e.g., min > max)
- User confusion

**Example:**
```blade
<!-- Price range with minimum $100 gap -->
<flux:slider range min="0" max="1000" step="10" min-steps-between="10" />
<!-- Gap = 10 steps × $10 = $100 -->
```

---

### Displaying Range Values

Display both range values using `wire:text` directives.

```blade
<flux:field>
    <flux:label>
        Price range
        <x-slot name="trailing">
            $<span wire:text="range[0]" class="tabular-nums"></span>
            &ndash;
            $<span wire:text="range[1]" class="tabular-nums"></span>
        </x-slot>
    </flux:label>
    <flux:slider 
        range 
        wire:model="range" 
        min="0" 
        max="990" 
        step="10" 
        min-steps-between="10" 
        big-step="100" />
</flux:field>
```

---

## Custom Styles

Customize track and thumb appearance.

```blade
<flux:slider track:class="h-5" thumb:class="size-5" />
```

**Common customizations:**
```blade
<!-- Larger slider -->
<flux:slider track:class="h-6 rounded-lg" thumb:class="size-6" />

<!-- Thinner slider -->
<flux:slider track:class="h-2" thumb:class="size-4" />

<!-- Custom colors (use Tailwind arbitrary values) -->
<flux:slider 
    track:class="bg-blue-100 dark:bg-blue-900" 
    thumb:class="bg-blue-600 dark:bg-blue-400" />
```

---

## API Reference

### flux:slider

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `wire:model` | string | - | Livewire property to bind to |
| `range` | boolean | `false` | Enable range selection with two thumbs |
| `min` | number | `0` | Minimum value |
| `max` | number | `100` | Maximum value |
| `step` | number | `1` | Increment size for value changes |
| `big-step` | number | - | Step size when holding Shift + arrow keys |
| `min-steps-between` | number | - | Minimum distance between thumbs (in steps) for range slider |
| `value` | string/number | - | Initial value. For range: comma-separated (e.g., "20,80") |
| `track:class` | string | - | CSS classes applied to the track |
| `thumb:class` | string | - | CSS classes applied to the thumb(s) |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | `flux:slider.tick` components for step markers |

---

### flux:slider.tick

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | number | - | **Required** Value position for the tick mark |

**Slots:**
| Slot | Description |
|------|-------------|
| `default` | Label text. If empty, displays a horizontal line tick |

---

## Usage Guidelines

**When to use Slider:**
- Adjusting settings/preferences (volume, brightness)
- Filtering by numeric ranges (price, age, date)
- Selecting values within defined bounds
- Visual value selection is preferred
- Approximate values are acceptable

**When NOT to use:**
- Precise values required (use input instead)
- Very large value ranges (>1000 steps)
- Date/time selection (use date-picker)
- Few discrete options (<5 - use radio buttons)

**Best Practices:**
- Always show current value(s)
- Use step marks for <10 steps
- Provide input alternative for precision
- Set sensible min/max/step values
- Use `big-step` for large ranges
- Include labels for context
- Test keyboard navigation
- Consider `min-steps-between` for ranges

---

## Common Patterns

**Basic Setting:**
```blade
<flux:field>
    <flux:label>
        Volume
        <x-slot name="trailing">{{ $volume }}%</x-slot>
    </flux:label>
    <flux:slider wire:model="volume" min="0" max="100" />
</flux:field>
```

**Price Range Filter:**
```blade
<flux:field>
    <flux:label>
        Price
        <x-slot name="trailing">
            ${{ number_format($priceRange[0]) }} - ${{ number_format($priceRange[1]) }}
        </x-slot>
    </flux:label>
    <flux:slider 
        range 
        wire:model.live="priceRange" 
        min="0" 
        max="10000" 
        step="100" 
        min-steps-between="5" />
</flux:field>
```

**Rating Selector:**
```blade
<flux:field>
    <flux:label>Minimum Rating</flux:label>
    <flux:slider wire:model="minRating" min="1" max="5" step="1">
        @foreach(range(1, 5) as $stars)
            <flux:slider.tick :value="$stars">
                {{ str_repeat('⭐', $stars) }}
            </flux:slider.tick>
        @endforeach
    </flux:slider>
</flux:field>
```

**Priority Selector:**
```blade
<flux:field>
    <flux:label>Priority Level</flux:label>
    <flux:slider wire:model="priority" min="1" max="4">
        <flux:slider.tick value="1">
            <flux:badge color="blue" size="sm">Low</flux:badge>
        </flux:slider.tick>
        <flux:slider.tick value="2">
            <flux:badge color="yellow" size="sm">Medium</flux:badge>
        </flux:slider.tick>
        <flux:slider.tick value="3">
            <flux:badge color="orange" size="sm">High</flux:badge>
        </flux:slider.tick>
        <flux:slider.tick value="4">
            <flux:badge color="red" size="sm">Critical</flux:badge>
        </flux:slider.tick>
    </flux:slider>
</flux:field>
```

**Date Range (Days Ago):**
```blade
<flux:field>
    <flux:label>
        Time Range
        <x-slot name="trailing">
            Last {{ $daysRange[0] }}-{{ $daysRange[1] }} days
        </x-slot>
    </flux:label>
    <flux:slider 
        range 
        wire:model.live="daysRange" 
        min="0" 
        max="365" 
        step="7" 
        min-steps-between="1">
        <flux:slider.tick value="0">Today</flux:slider.tick>
        <flux:slider.tick value="30">30d</flux:slider.tick>
        <flux:slider.tick value="90">90d</flux:slider.tick>
        <flux:slider.tick value="180">6mo</flux:slider.tick>
        <flux:slider.tick value="365">1yr</flux:slider.tick>
    </flux:slider>
</flux:field>
```

**With Dual Input:**
```blade
<flux:field>
    <flux:label>Price Range</flux:label>
    <div class="space-y-2">
        <flux:slider 
            range 
            wire:model.live="priceRange" 
            min="0" 
            max="1000" 
            step="10" />
        
        <div class="flex items-center gap-2">
            <flux:input 
                wire:model="priceRange.0" 
                type="number" 
                size="sm" 
                placeholder="Min" 
                class="flex-1" />
            <span class="text-zinc-500">to</span>
            <flux:input 
                wire:model="priceRange.1" 
                type="number" 
                size="sm" 
                placeholder="Max" 
                class="flex-1" />
        </div>
    </div>
</flux:field>
```

---

## Accessibility

**Keyboard Navigation:**
- `Tab` - Focus slider
- `←/→` - Decrease/increase by `step`
- `↓/↑` - Decrease/increase by `step`
- `Shift + ←/→` - Move by `big-step`
- `Home` - Jump to minimum
- `End` - Jump to maximum
- `Page Down/Up` - Move by larger increments

**For range sliders:**
- `Tab` cycles between thumbs
- Both thumbs fully keyboard accessible

**Best practices:**
- Always include labels
- Ensure sufficient color contrast
- Provide alternative input method
- Test with screen readers

---

**Reference:** https://fluxui.dev/components/slider
