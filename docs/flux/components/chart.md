# Flux Chart Component Reference

Lightweight, zero-dependency charts for Livewire. Build charts by composing `flux:chart.*` primitives.

## Data Input

Use either `wire:model` or `:value`.

```php
<?php

use Livewire\Component;

class Dashboard extends Component
{
    public array $data = [
        ['date' => '2026-02-15', 'visitors' => 267, 'views' => 512],
        ['date' => '2026-02-14', 'visitors' => 259, 'views' => 498],
        ['date' => '2026-02-13', 'visitors' => 269, 'views' => 530],
    ];
}
```

```blade
<flux:chart wire:model="data" />
<flux:chart :value="$this->data" />
<flux:chart :value="[1, 2, 3, 4, 5]" /> {{-- sparkline-style --}}
```

## Quick Start (Line + Axis + Tooltip)

```blade
<flux:chart wire:model="data" class="aspect-[3/1]">
    <flux:chart.svg>
        <flux:chart.line field="visitors" class="text-pink-500 dark:text-pink-400" />

        <flux:chart.axis axis="x" field="date">
            <flux:chart.axis.line />
            <flux:chart.axis.tick />
        </flux:chart.axis>

        <flux:chart.axis axis="y">
            <flux:chart.axis.grid />
            <flux:chart.axis.tick />
        </flux:chart.axis>

        <flux:chart.cursor />
    </flux:chart.svg>

    <flux:chart.tooltip>
        <flux:chart.tooltip.heading field="date" :format="['month' => 'short', 'day' => 'numeric']" />
        <flux:chart.tooltip.value field="visitors" label="Visitors" />
    </flux:chart.tooltip>
</flux:chart>
```

## Common Chart Patterns

### Area

```blade
<flux:chart wire:model="data" class="aspect-[3/1]">
    <flux:chart.svg>
        <flux:chart.line field="views" class="text-blue-500" curve="none" />
        <flux:chart.area field="views" class="text-blue-200/50" curve="none" />
    </flux:chart.svg>
</flux:chart>
```

### Multi-line + Legend

Use `flux:chart.viewport` when rendering siblings (legend/summary) outside the SVG.

```blade
<flux:chart wire:model="data">
    <flux:chart.viewport class="aspect-[3/1]">
        <flux:chart.svg>
            <flux:chart.line field="visitors" class="text-sky-500" />
            <flux:chart.line field="views" class="text-emerald-500" />

            <flux:chart.axis axis="x" field="date"><flux:chart.axis.tick /></flux:chart.axis>
            <flux:chart.axis axis="y"><flux:chart.axis.grid /><flux:chart.axis.tick /></flux:chart.axis>
        </flux:chart.svg>
    </flux:chart.viewport>

    <div class="flex justify-center gap-4 pt-4">
        <flux:chart.legend label="Visitors"><flux:chart.legend.indicator class="bg-sky-400" /></flux:chart.legend>
        <flux:chart.legend label="Views"><flux:chart.legend.indicator class="bg-emerald-400" /></flux:chart.legend>
    </div>
</flux:chart>
```

### Bar

```blade
<flux:chart wire:model="data" class="aspect-[3/1]">
    <flux:chart.svg>
        <flux:chart.bar field="views" class="text-blue-500" width="85%" radius="0" />

        <flux:chart.axis axis="x" field="date" tick-count="8">
            <flux:chart.axis.tick />
            <flux:chart.axis.line />
        </flux:chart.axis>

        <flux:chart.axis axis="y" tick-prefix="$" :format="['useGrouping' => true]">
            <flux:chart.axis.grid />
            <flux:chart.axis.tick />
        </flux:chart.axis>

        <flux:chart.cursor type="area" />
    </flux:chart.svg>

    <flux:chart.tooltip>
        <flux:chart.tooltip.heading field="date" />
        <flux:chart.tooltip.value field="views" label="Views" :format="['useGrouping' => true]" />
    </flux:chart.tooltip>
</flux:chart>
```

### Grouped & Stacked Bars

```blade
{{-- Grouped --}}
<flux:chart wire:model="data">
    <flux:chart.svg>
        <flux:chart.group>
            <flux:chart.bar field="visitors" class="text-blue-600" />
            <flux:chart.bar field="views" class="text-blue-400" />
        </flux:chart.group>
    </flux:chart.svg>
</flux:chart>

{{-- Stacked --}}
<flux:chart wire:model="data">
    <flux:chart.svg>
        <flux:chart.stack width="65%">
            <flux:chart.bar field="visitors" class="text-blue-600" />
            <flux:chart.bar field="views" class="text-blue-300" />
        </flux:chart.stack>
    </flux:chart.svg>
</flux:chart>
```

### Sparkline

Use `gutter="0"` to remove default chart padding.

```blade
<flux:chart :value="[15,18,16,19,22,25,28,25,29,28,32,35]" class="w-[5rem] aspect-[3/1]">
    <flux:chart.svg gutter="0">
        <flux:chart.line class="text-green-500" />
    </flux:chart.svg>
</flux:chart>
```

## Interaction: Tooltip, Cursor, Summary

```blade
<flux:chart wire:model="data" class="grid gap-4">
    <flux:chart.summary class="flex gap-8">
        <div>
            <flux:text>Visitors</flux:text>
            <flux:heading size="lg" class="tabular-nums">
                <flux:chart.summary.value field="visitors" fallback="0" :format="['notation' => 'compact']" />
            </flux:heading>
        </div>
    </flux:chart.summary>

    <flux:chart.viewport class="aspect-[3/1]">
        <flux:chart.svg>
            <flux:chart.line field="visitors" class="text-sky-500" />
            <flux:chart.axis axis="x" field="date"><flux:chart.axis.tick /></flux:chart.axis>
            <flux:chart.axis axis="y"><flux:chart.axis.tick /></flux:chart.axis>
            <flux:chart.cursor />
        </flux:chart.svg>
    </flux:chart.viewport>

    <flux:chart.tooltip>
        <flux:chart.tooltip.heading field="date" :format="['hour' => 'numeric', 'minute' => 'numeric']" />
        <flux:chart.tooltip.value field="visitors" label="Visitors" />
    </flux:chart.tooltip>
</flux:chart>
```

## Axis & Tick Controls

```blade
<flux:chart.axis axis="y" scale="linear" />
<flux:chart.axis axis="x" scale="time" field="date" />

<flux:chart.axis axis="y" tick-count="5" />
<flux:chart.axis axis="y" tick-start="min" tick-end="max" />
<flux:chart.axis axis="y" tick-values="[0, 250, 500, 750, 1000]" />

<flux:chart.axis axis="y" tick-prefix="$" tick-suffix="K" />

<flux:chart.zero-line class="text-zinc-800" stroke-width="2" />
<flux:chart.axis.grid class="text-zinc-200/50" stroke-dasharray="4,4" />
<flux:chart.axis.mark class="text-zinc-300" y1="0" y2="8" />
```

## Formatting

Flux uses browser Intl APIs:
- Numbers: `Intl.NumberFormat`
- Dates: `Intl.DateTimeFormat`

```blade
{{-- Number formatting --}}
<flux:chart.axis axis="y" :format="['style' => 'currency', 'currency' => 'USD']" />
<flux:chart.axis axis="y" :format="['style' => 'percent']" />
<flux:chart.axis axis="y" :format="['notation' => 'compact']" />
<flux:chart.axis axis="y" :format="['maximumFractionDigits' => 2]" />
<flux:chart.axis axis="y" :format="['style' => 'unit', 'unit' => 'megabyte']" />

{{-- Date formatting --}}
<flux:chart.axis axis="x" field="date" :format="['dateStyle' => 'full']" />
<flux:chart.axis axis="x" field="date" :format="['month' => 'short', 'day' => 'numeric']" />
<flux:chart.axis axis="x" field="date" :format="['hour' => '2-digit', 'minute' => '2-digit', 'hour12' => false]" />
```

## Compact API Reference

### `flux:chart`

| Prop | Notes |
|---|---|
| `wire:model` | Bind chart data from Livewire property |
| `value` | Pass data directly without binding |
| `curve` | Default line curve: `smooth` (default), `none` |
| `class` | Container sizing/layout classes |

### `flux:chart.svg`

| Prop | Notes |
|---|---|
| `gutter` | Chart padding. Default `8px`; use `0` for sparklines |

### Series components

| Component | Key props |
|---|---|
| `flux:chart.line` | `field` (required), `curve`, `class` |
| `flux:chart.area` | `field` (required), `curve`, `class` |
| `flux:chart.point` | `field`, SVG attrs (`r`, `stroke-width`, etc.) |
| `flux:chart.bar` | `field`, `width`, `radius`, `class` |
| `flux:chart.group` | Wrap bars for grouped bars |
| `flux:chart.stack` | Wrap bars for stacked bars (`width` optional) |

### Axis components

| Component | Key props |
|---|---|
| `flux:chart.axis` | `axis` (`x`/`y`), `field`, `scale`, `position`, `tick-*`, `:format` |
| `flux:chart.axis.tick` | Tick labels |
| `flux:chart.axis.mark` | Tick lines |
| `flux:chart.axis.line` | Axis baseline |
| `flux:chart.axis.grid` | Grid lines |
| `flux:chart.zero-line` | Horizontal zero reference line |

### Interaction & metadata

| Component | Key props |
|---|---|
| `flux:chart.cursor` | Hover guide line |
| `flux:chart.tooltip.heading` | `field`, `:format` |
| `flux:chart.tooltip.value` | `field`, `label`, `:format`, `prefix`, `suffix` |
| `flux:chart.summary.value` | `field`, `fallback`, `:format` |
| `flux:chart.legend` | `label` (+ optional indicator slot) |

## Practical Rules

- Use `flux:chart.viewport` whenever summary/legend is outside SVG.
- Use `gutter="0"` for tiny inline charts.
- Add `flux:chart.cursor` + `flux:chart.tooltip` together for best hover UX.
- Use `tick-values` when you need exact tick positions.
- Prefer `:format` over custom string formatting.

**Reference**: https://fluxui.dev/components/chart
