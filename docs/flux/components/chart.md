# Chart Component

Lightweight, zero-dependency tool for building charts in Livewire applications. Currently supports line and area charts with flexible composition and styling.

## Data Structure

Charts expect a structured array of data via `wire:model` or `:value` prop:

```php
<?php
use Livewire\Component;

class Dashboard extends Component
{
    public array $data = [
        ['date' => '2025-11-21', 'visitors' => 267],
        ['date' => '2025-11-20', 'visitors' => 259],
        ['date' => '2025-11-19', 'visitors' => 269],
        // ...
    ];
}
```

**Binding data:**
```blade
<!-- Using wire:model -->
<flux:chart wire:model="data" />

<!-- Using :value prop -->
<flux:chart :value="$this->data" />

<!-- Simple array for sparklines -->
<flux:chart :value="[1, 2, 3, 4, 5]" />
```

## Basic Line Chart

```blade
<flux:chart wire:model="data" class="aspect-[3/1]">
    <flux:chart.svg>
        <flux:chart.line field="visitors" class="text-pink-500" />
        <flux:chart.point field="visitors" class="text-pink-400" />
        
        <flux:chart.axis axis="x" field="date">
            <flux:chart.axis.tick />
            <flux:chart.axis.line />
        </flux:chart.axis>
        
        <flux:chart.axis axis="y" tick-values="[0, 128, 256, 384, 512]" :format="['style' => 'unit', 'unit' => 'megabyte']">
            <flux:chart.axis.grid />
            <flux:chart.axis.tick />
        </flux:chart.axis>
    </flux:chart.svg>
</flux:chart>
```

## Area Chart

Line chart with filled area beneath:

```blade
<flux:chart wire:model="data" class="aspect-3/1">
    <flux:chart.svg>
        <flux:chart.line field="stock" class="text-blue-500" curve="none" />
        <flux:chart.area field="stock" class="text-blue-200/50" curve="none" />
        
        <flux:chart.axis axis="y" position="right" tick-prefix="$" :format="['notation' => 'compact', 'maximumFractionDigits' => 1]">
            <flux:chart.axis.grid />
            <flux:chart.axis.tick />
        </flux:chart.axis>
        
        <flux:chart.axis axis="x" field="date">
            <flux:chart.axis.tick />
            <flux:chart.axis.line />
        </flux:chart.axis>
    </flux:chart.svg>
</flux:chart>
```

## Multiple Lines

Compare different data series:

```blade
<flux:chart wire:model="data">
    <flux:chart.viewport class="min-h-[20rem]">
        <flux:chart.svg>
            <flux:chart.line field="twitter" class="text-blue-500" curve="none" />
            <flux:chart.point field="twitter" class="text-blue-500" r="6" stroke-width="3" />
            
            <flux:chart.line field="facebook" class="text-red-500" curve="none" />
            <flux:chart.point field="facebook" class="text-red-500" r="6" stroke-width="3" />
            
            <flux:chart.line field="instagram" class="text-green-500" curve="none" />
            <flux:chart.point field="instagram" class="text-green-500" r="6" stroke-width="3" />
            
            <flux:chart.axis axis="x" field="date">
                <flux:chart.axis.tick />
                <flux:chart.axis.line />
            </flux:chart.axis>
            
            <flux:chart.axis axis="y" tick-start="0" tick-end="1" :format="['style' => 'percent', 'minimumFractionDigits' => 0]">
                <flux:chart.axis.grid />
                <flux:chart.axis.tick />
            </flux:chart.axis>
        </flux:chart.svg>
    </flux:chart.viewport>
    
    <div class="flex justify-center gap-4 pt-4">
        <flux:chart.legend label="Instagram">
            <flux:chart.legend.indicator class="bg-green-400" />
        </flux:chart.legend>
        <flux:chart.legend label="Twitter">
            <flux:chart.legend.indicator class="bg-blue-400" />
        </flux:chart.legend>
        <flux:chart.legend label="Facebook">
            <flux:chart.legend.indicator class="bg-red-400" />
        </flux:chart.legend>
    </div>
</flux:chart>
```

## Live Summary

Display values that update on hover:

```blade
<flux:card>
    <flux:chart class="grid gap-6" wire:model="data">
        <flux:chart.summary class="flex gap-12">
            <div>
                <flux:text>Today</flux:text>
                <flux:heading size="xl" class="mt-2 tabular-nums">
                    <flux:chart.summary.value field="sales" :format="['style' => 'currency', 'currency' => 'USD']" />
                </flux:heading>
                <flux:text class="mt-2 tabular-nums">
                    <flux:chart.summary.value field="date" :format="['hour' => 'numeric', 'minute' => 'numeric', 'hour12' => true]" />
                </flux:text>
            </div>
            <div>
                <flux:text>Yesterday</flux:text>
                <flux:heading size="lg" class="mt-2 tabular-nums">
                    <flux:chart.summary.value field="yesterday" :format="['style' => 'currency', 'currency' => 'USD']" />
                </flux:heading>
            </div>
        </flux:chart.summary>
        
        <flux:chart.viewport class="aspect-[3/1]">
            <flux:chart.svg>
                <flux:chart.line field="yesterday" class="text-zinc-300" stroke-dasharray="4 4" curve="none" />
                <flux:chart.line field="sales" class="text-sky-500" curve="none" />
                
                <flux:chart.axis axis="x" field="date">
                    <flux:chart.axis.grid />
                    <flux:chart.axis.tick />
                    <flux:chart.axis.line />
                </flux:chart.axis>
                
                <flux:chart.axis axis="y">
                    <flux:chart.axis.tick />
                </flux:chart.axis>
                
                <flux:chart.cursor />
            </flux:chart.svg>
        </flux:chart.viewport>
    </flux:chart>
</flux:card>
```

## Sparkline

Compact single-line chart for tables/dashboards:

```blade
<flux:chart :value="[15, 18, 16, 19, 22, 25, 28, 25, 29, 28, 32, 35]" class="w-[5rem] aspect-[3/1]">
    <flux:chart.svg gutter="0">
        <flux:chart.line class="text-green-500" />
    </flux:chart.svg>
</flux:chart>
```

## Dashboard Stat

KPI card with background chart:

```blade
<flux:card class="overflow-hidden min-w-[12rem]">
    <flux:text>Revenue</flux:text>
    <flux:heading size="xl" class="mt-2 tabular-nums">$12,345</flux:heading>
    
    <flux:chart class="-mx-8 -mb-8 h-[3rem]" :value="[10, 12, 11, 13, 15, 14, 16, 18, 17, 19, 21, 20]">
        <flux:chart.svg gutter="0">
            <flux:chart.line class="text-sky-200" />
            <flux:chart.area class="text-sky-100" />
        </flux:chart.svg>
    </flux:chart>
</flux:card>
```

## Interactive Features

### Tooltip
```blade
<flux:chart>
    <flux:chart.svg>
        <!-- ... chart elements ... -->
    </flux:chart.svg>
    
    <flux:chart.tooltip>
        <flux:chart.tooltip.heading field="date" />
        <flux:chart.tooltip.value field="visitors" label="Visitors" />
        <flux:chart.tooltip.value field="views" label="Views" :format="['notation' => 'compact']" />
    </flux:chart.tooltip>
</flux:chart>
```

### Cursor
```blade
<flux:chart.svg>
    <!-- ... chart elements ... -->
    <flux:chart.cursor />
</flux:chart.svg>

<!-- Styled cursor -->
<flux:chart.cursor class="text-zinc-800" stroke-width="1" stroke-dasharray="4,4" />
```

## Axis Configuration

### Tick Frequency
```blade
<!-- Set tick count -->
<flux:chart.axis axis="y" tick-count="5">
    <flux:chart.axis.tick />
</flux:chart.axis>

<!-- Set start/end -->
<flux:chart.axis axis="y" tick-start="min" tick-end="max">
    <flux:chart.axis.tick />
</flux:chart.axis>

<!-- Explicit values -->
<flux:chart.axis axis="y" tick-values="[0, 128, 256, 384, 512]" tick-suffix="MB">
    <flux:chart.axis.tick />
</flux:chart.axis>
```

### Axis Scale
```blade
<!-- Options: categorical, linear, time -->
<flux:chart.axis axis="y" scale="linear">
    <flux:chart.axis.tick />
</flux:chart.axis>
```

### Grid Lines & Axis Lines
```blade
<flux:chart.axis axis="x">
    <flux:chart.axis.grid />  <!-- Vertical grid lines -->
    <flux:chart.axis.line />  <!-- X axis line -->
    <flux:chart.axis.tick />
</flux:chart.axis>

<flux:chart.axis axis="y">
    <flux:chart.axis.grid />  <!-- Horizontal grid lines -->
    <flux:chart.axis.line />  <!-- Y axis line -->
    <flux:chart.axis.tick />
</flux:chart.axis>

<!-- Styled grid -->
<flux:chart.axis.grid class="text-zinc-200/50" stroke-width="2" stroke-dasharray="4,4" />
```

### Zero Line
```blade
<flux:chart.svg>
    <!-- ... chart elements ... -->
    <flux:chart.zero-line />
</flux:chart.svg>

<!-- Styled -->
<flux:chart.zero-line class="text-zinc-800" stroke-width="2" />
```

## Formatting

Charts use `Intl.NumberFormat` and `Intl.DateTimeFormat` for formatting.

### Number Formatting
```blade
<!-- Currency -->
<flux:chart.axis axis="y" :format="['style' => 'currency', 'currency' => 'USD']" />

<!-- Percent -->
<flux:chart.axis axis="y" :format="['style' => 'percent']" />

<!-- Compact -->
<flux:chart.axis axis="y" :format="['notation' => 'compact']" />

<!-- Fixed decimals -->
<flux:chart.axis axis="y" :format="['maximumFractionDigits' => 2]" />

<!-- Custom unit -->
<flux:chart.axis axis="y" :format="['style' => 'unit', 'unit' => 'megabyte']" />

<!-- Prefix/Suffix -->
<flux:chart.axis axis="y" tick-prefix="$" />
<flux:chart.axis axis="y" tick-suffix="MB" />
```

### Date Formatting
```blade
<!-- Full date -->
<flux:chart.axis axis="x" field="date" :format="['dateStyle' => 'full']" />

<!-- Month and day -->
<flux:chart.axis axis="x" field="date" :format="['month' => 'long', 'day' => 'numeric']" />

<!-- Short month -->
<flux:chart.axis axis="x" field="date" :format="['month' => 'short', 'day' => 'numeric']" />

<!-- Time -->
<flux:chart.axis axis="x" field="date" :format="['hour' => 'numeric', 'minute' => 'numeric', 'hour12' => true]" />

<!-- Year only -->
<flux:chart.axis axis="x" field="date" :format="['year' => 'numeric']" />
```

## Chart Padding

Control SVG padding with `gutter` attribute (default: 8px all sides):

```blade
<!-- No padding (for sparklines) -->
<flux:chart.svg gutter="0">

<!-- Custom padding (top right bottom left) -->
<flux:chart.svg gutter="12 0 12 8">
```

## Layout Components

### flux:chart.viewport
Use `viewport` to constrain chart SVG when rendering legends/summaries:

```blade
<flux:chart wire:model="data">
    <flux:chart.viewport class="aspect-3/1">
        <flux:chart.svg>
            <!-- ... chart elements ... -->
        </flux:chart.svg>
    </flux:chart.viewport>
    
    <!-- Legend/summary outside viewport -->
    <div class="flex justify-center gap-4 pt-4">
        <!-- ... legends ... -->
    </div>
</flux:chart>
```

## Key Properties

### flux:chart
| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property containing data |
| `value` | Array of data points (alternative to wire:model) |
| `curve` | Default line curve: `smooth` (default), `none` |

### flux:chart.line / flux:chart.area
| Property | Description |
|----------|-------------|
| `field` | Data field to plot (required) |
| `curve` | Curve type: `smooth`, `none` |
| `class` | Styling (e.g., `text-blue-500` for line color) |

### flux:chart.axis
| Property | Description |
|----------|-------------|
| `axis` | `x` or `y` (required) |
| `field` | Data field for labels (x-axis) |
| `scale` | `categorical`, `linear`, `time` |
| `position` | `top`, `bottom`, `left`, `right` |
| `tick-count` | Target number of ticks |
| `tick-start` | Starting tick: `auto`, `min`, `0`, or number |
| `tick-end` | Ending tick: `auto`, `max`, or number |
| `tick-values` | Explicit tick values array |
| `tick-prefix` | Prefix for tick labels (e.g., `"$"`) |
| `tick-suffix` | Suffix for tick labels (e.g., `"MB"`) |
| `:format` | Number/date formatting options |

### flux:chart.tooltip.value
| Property | Description |
|----------|-------------|
| `field` | Data field to display |
| `label` | Label text |
| `:format` | Formatting options |

### flux:chart.summary.value
| Property | Description |
|----------|-------------|
| `field` | Data field to display |
| `fallback` | Value when not hovering |
| `:format` | Formatting options |

## Guidelines

- Use `flux:chart.viewport` when adding legends/summaries outside chart
- Set `gutter="0"` for sparklines to remove padding
- Default curve is `smooth`, use `curve="none"` for linear lines
- X-axis auto-detects time scale for date fields
- Use `tick-values` for precise tick positioning
- Apply SVG attributes for styling (stroke-width, stroke-dasharray, etc.)
- Combine cursor + tooltip for interactive charts

**Reference**: https://fluxui.dev/components/chart
