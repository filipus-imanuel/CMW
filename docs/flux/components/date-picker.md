# Date Picker Component

Allow users to select dates or date ranges via a calendar overlay. Perfect for filtering data or scheduling events.

## Basic Usage

### Single Date

```blade
<!-- Simple date picker -->
<flux:date-picker />

<!-- With initial value -->
<flux:date-picker value="2025-11-21" />

<!-- Bind to Livewire property -->
<flux:date-picker wire:model="date" />
```

**Livewire Component:**
```php
<?php
use Illuminate\Support\Carbon;
use Livewire\Component;

class CreatePost extends Component {
    public ?Carbon $date;
    
    public function save() {
        Post::create([
            'published_at' => $this->date,
        ]);
    }
}
```

### With Input Trigger

```blade
<flux:date-picker wire:model="date">
    <x-slot name="trigger">
        <flux:date-picker.input />
    </x-slot>
</flux:date-picker>
```

## Range Picker

Select start and end dates:

```blade
<!-- Basic range picker -->
<flux:date-picker mode="range" />

<!-- With initial range (start/end separated by /) -->
<flux:date-picker mode="range" value="2025-11-02/2025-11-06" />

<!-- Bind to Livewire property -->
<flux:date-picker mode="range" wire:model="range" />
```

### Array Binding

```php
<?php
use Livewire\Component;

class Dashboard extends Component {
    public array $range;
    
    public function mount() {
        $this->range = [
            'start' => now()->subMonth()->format('Y-m-d'),
            'end' => now()->format('Y-m-d'),
        ];
    }
}
```

### DateRange Object (Recommended)

```php
<?php
use Livewire\Component;
use Flux\DateRange;

class Dashboard extends Component {
    public DateRange $range;
    
    public function mount() {
        $this->range = new DateRange(now()->subMonth(), now());
    }
}
```

### Range with Separate Inputs

```blade
<flux:date-picker mode="range">
    <x-slot name="trigger">
        <div class="flex flex-col sm:flex-row gap-6 sm:gap-4">
            <flux:date-picker.input label="Start" />
            <flux:date-picker.input label="End" />
        </div>
    </x-slot>
</flux:date-picker>
```

## Range Limits

```blade
<!-- Minimum range (days) -->
<flux:date-picker mode="range" min-range="3" />

<!-- Maximum range (days) -->
<flux:date-picker mode="range" max-range="10" />
```

## Presets

Quick selection for common date ranges:

```blade
<!-- Default presets -->
<flux:date-picker mode="range" with-presets />

<!-- Custom presets -->
<flux:date-picker 
    mode="range" 
    presets="today yesterday thisWeek last7Days thisMonth yearToDate allTime" 
/>
```

### Available Presets

| Preset | Label | Static Method | Description |
|--------|-------|---------------|-------------|
| `today` | Today | `DateRange::today()` | Current day |
| `yesterday` | Yesterday | `DateRange::yesterday()` | Previous day |
| `thisWeek` | This Week | `DateRange::thisWeek()` | Current week |
| `lastWeek` | Last Week | `DateRange::lastWeek()` | Previous week |
| `last7Days` | Last 7 Days | `DateRange::last7Days()` | Previous 7 days |
| `last14Days` | Last 14 Days | `DateRange::last14Days()` | Previous 14 days |
| `last30Days` | Last 30 Days | `DateRange::last30Days()` | Previous 30 days |
| `thisMonth` | This Month | `DateRange::thisMonth()` | Current month |
| `lastMonth` | Last Month | `DateRange::lastMonth()` | Previous month |
| `last3Months` | Last 3 Months | `DateRange::last3Months()` | Previous 3 months |
| `last6Months` | Last 6 Months | `DateRange::last6Months()` | Previous 6 months |
| `thisQuarter` | This Quarter | `DateRange::thisQuarter()` | Current quarter |
| `lastQuarter` | Last Quarter | `DateRange::lastQuarter()` | Previous quarter |
| `thisYear` | This Year | `DateRange::thisYear()` | Current year |
| `lastYear` | Last Year | `DateRange::lastYear()` | Previous year |
| `yearToDate` | Year to Date | `DateRange::yearToDate()` | Jan 1 to today |
| `allTime` | All Time | `DateRange::allTime($start)` | Supplied date to today |
| `custom` | Custom Range | `DateRange::custom()` | User-defined |

### All Time Preset

Requires `min` prop:

```blade
<flux:date-picker 
    mode="range" 
    presets="today thisWeek thisMonth allTime" 
    :min="auth()->user()->created_at->format('Y-m-d')" 
/>
```

```php
// Via DateRange
$this->range = DateRange::allTime(start: auth()->user()->created_at);

// Check if allTime selected
$orders = Order::when($this->range->isNotAllTime(), function($query) {
    $query->whereBetween('created_at', $this->range);
})->get();
```

## Additional Features

### Unavailable Dates

```blade
<flux:date-picker unavailable="2025-11-20,2025-11-22" />
```

### Today Shortcut

```blade
<flux:date-picker with-today />
```

### Selectable Header

Quick month/year navigation:

```blade
<flux:date-picker selectable-header />
```

### Fixed Weeks

Consistent calendar height:

```blade
<flux:date-picker fixed-weeks />
```

### Week Numbers

```blade
<flux:date-picker week-numbers />
```

### Start Day

```blade
<!-- 0 = Sunday, 1 = Monday, etc. -->
<flux:date-picker start-day="1" />
```

### Open To Specific Date

```blade
<flux:date-picker open-to="2026-12-01" />
```

### Localization

```blade
<flux:date-picker locale="ja-JP" />
```

## DateRange Object

Extends `CarbonPeriod` with convenience methods.

### Instantiation

```php
use Flux\DateRange;

// Constructor
$range = new DateRange(now(), now()->addDays(7));

// Static methods
$range = DateRange::lastMonth();
$range = DateRange::last7Days();
```

### Persisting to Session

```php
use Livewire\Attributes\Session;
use Flux\DateRange;

class Dashboard extends Component {
    #[Session]
    public DateRange $range;
}
```

### Using with Eloquent

```php
use Livewire\Attributes\Computed;
use Flux\DateRange;

class Dashboard extends Component {
    public ?DateRange $range;
    
    #[Computed]
    public function orders() {
        return $this->range
            ? Order::whereBetween('created_at', $this->range)->get()
            : Order::all();
    }
}
```

### Available Methods

```php
$range = new DateRange(now()->subDays(1), now()->addDays(1));

// Get start/end as Carbon
$start = $range->start();
$end = $range->end();

// Check if contains date
$range->contains(now());

// Get length
$range->length();

// Loop through range
foreach ($range as $date) {
    // $date is Carbon instance
}

// Convert to array
$range->toArray();

// Get preset
$preset = $range->preset(); // Returns DateRangePreset enum
```

## Properties

### flux:date-picker

| Property | Description |
|----------|-------------|
| `wire:model` | Bind to Livewire property |
| `value` | Selected date(s): `Y-m-d` (single) or `Y-m-d/Y-m-d` (range) |
| `mode` | `single` (default), `range` |
| `min-range` | Minimum days in range mode |
| `max-range` | Maximum days in range mode |
| `min` | Earliest selectable date (date string or `"today"`) |
| `max` | Latest selectable date (date string or `"today"`) |
| `months` | Number of months to display (default: 1 single, 2 range) |
| `label` | Label text (wraps in `flux:field`) |
| `description` | Help text below picker |
| `description:trailing` | Help text below instead of above |
| `badge` | Badge on label |
| `placeholder` | Text when no date selected |
| `size` | Cell size: `sm`, `default`, `lg`, `xl`, `2xl` |
| `start-day` | First day of week: 0-6 (default: locale-based) |
| `week-numbers` | Show week numbers |
| `selectable-header` | Month/year dropdowns |
| `with-today` | Today shortcut button |
| `with-inputs` | Manual date entry inputs |
| `with-confirmation` | Require confirmation before applying |
| `with-presets` | Show preset ranges |
| `presets` | Space-separated preset list |
| `unavailable` | Comma-separated dates to disable |
| `clearable` | Show clear button |
| `disabled` | Disable interaction |
| `invalid` | Error styling |
| `locale` | Locale string (e.g., `fr`, `en-US`) |
| `open-to` | Initial month to display |

### flux:date-picker.input

| Property | Description |
|----------|-------------|
| `label` | Label text |
| `description` | Help text |
| `placeholder` | Placeholder text |
| `clearable` | Show clear button |
| `disabled` | Disable input |
| `invalid` | Error styling |

### flux:date-picker.button

| Property | Description |
|----------|-------------|
| `placeholder` | Text when no date selected |
| `size` | `sm`, `xs` |
| `clearable` | Show clear button |
| `disabled` | Disable button |
| `invalid` | Error styling |

## Guidelines

- Use `Carbon` type hint for single date Livewire properties
- Use `DateRange` object for range mode (recommended over arrays)
- Use `wire:model.live` to reactively update when range changes
- Add `min` prop when using `allTime` preset
- Use `with-inputs` for precise manual date entry
- Use `fixed-weeks` to prevent layout shifts
- Default locale is browser-based (`navigator.language`)

**Reference**: https://fluxui.dev/components/date-picker
