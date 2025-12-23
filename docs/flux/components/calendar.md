# Flux Calendar Component

## Basic Usage

**Single Date Selection:**
```blade
<!-- Static value -->
<flux:calendar value="2025-11-21" />

<!-- Livewire binding with Carbon -->
<flux:calendar wire:model="date" />
```

```php
use Illuminate\Support\Carbon;

public Carbon $date;

public function mount() {
    $this->date = now();
}
```

## Selection Modes

**Multiple Dates:**
```blade
<flux:calendar multiple />
<flux:calendar multiple value="2025-11-02,2025-11-05,2025-11-15" />
<flux:calendar multiple wire:model="dates" />
```

```php
public array $dates = [];

public function mount() {
    $this->dates = [
        now()->format('Y-m-d'),
        now()->addDays(1)->format('Y-m-d'),
    ];
}
```

**Date Range:**
```blade
<flux:calendar mode="range" />
<flux:calendar mode="range" value="2025-11-02/2025-11-06" />
<flux:calendar mode="range" wire:model="range" />
```

```php
use Flux\DateRange;

public ?DateRange $range;

// Use specialized DateRange object (recommended)
public function mount() {
    $this->range = new DateRange(now(), now()->addDays(7));
}

// Or use array
public ?array $range; // ['start' => 'Y-m-d', 'end' => 'Y-m-d']
```

## Range Configuration

```blade
<!-- Set min/max range limits (days) -->
<flux:calendar mode="range" min-range="3" max-range="10" />

<!-- Control number of months shown -->
<flux:calendar mode="range" months="2" />
```

## Date Restrictions

**Min/Max Dates:**
```blade
<flux:calendar max="2025-11-21" />
<flux:calendar min="today" />  <!-- Prevent past dates -->
<flux:calendar max="today" />  <!-- Prevent future dates -->
```

**Unavailable Dates:**
```blade
<!-- Disable specific dates (comma-separated) -->
<flux:calendar unavailable="2025-11-20,2025-11-22" />
```

## Size Variants

```blade
<!-- Available: xs, sm, base (default), lg, xl, 2xl -->
<flux:calendar size="xl" />
```

## Display Options

**Static (Non-interactive):**
```blade
<flux:calendar static value="2025-11-21" size="xs" :navigation="false" />
```

**Today Shortcut:**
```blade
<flux:calendar with-today />
```

**Selectable Header:**
```blade
<!-- Month/year dropdown for quick navigation -->
<flux:calendar selectable-header />
```

**Week Numbers:**
```blade
<flux:calendar week-numbers />
```

**Fixed Weeks:**
```blade
<!-- Consistent layout (prevents month navigation shifts) -->
<flux:calendar fixed-weeks />
```

## Configuration

**Start Day:**
```blade
<!-- 0 = Sunday, 6 = Saturday (default: user locale) -->
<flux:calendar start-day="1" />  <!-- Monday -->
```

**Open To Date:**
```blade
<!-- Set initial month/year view -->
<flux:calendar open-to="2026-12-01" />
```

**Localization:**
```blade
<!-- Default: browser locale (navigator.language) -->
<flux:calendar locale="ja-JP" />
<flux:calendar locale="fr" />
<flux:calendar locale="en-US" />
```

## DateRange Object

**Recommended for range mode** - extends `CarbonPeriod` with convenience methods:

```php
use Flux\DateRange;
use Livewire\Attributes\Session;

class Dashboard extends Component
{
    // Use wire:model.live for real-time updates
    public DateRange $range;
    
    // Persist in session
    #[Session]
    public DateRange $range;
    
    public function mount() {
        $this->range = new DateRange(now(), now()->addDays(7));
    }
}
```

**Available Methods:**
```php
$range->start();           // Carbon instance
$range->end();             // Carbon instance
$range->length();          // Number of days
$range->contains(now());   // Check if date in range
$range->toArray();         // ['start' => Carbon, 'end' => Carbon]

// Loop over dates
foreach ($range as $date) {
    // $date is Carbon instance
}
```

**Static Constructors:**
```php
DateRange::today();
DateRange::yesterday();
DateRange::thisWeek();
DateRange::lastWeek();
DateRange::last7Days();
DateRange::thisMonth();
DateRange::lastMonth();
DateRange::thisYear();
DateRange::lastYear();
DateRange::yearToDate();
```

**Eloquent Integration:**
```php
use Flux\DateRange;

#[Computed]
public function orders()
{
    return $this->range
        ? Order::whereBetween('created_at', $this->range)->get()
        : Order::all();
}
```

## Key Props Reference

| Prop | Description | Values |
|------|-------------|--------|
| `wire:model` | Bind to Livewire property | - |
| `value` | Selected date(s) | Single: `Y-m-d`, Multiple: `Y-m-d,Y-m-d`, Range: `Y-m-d/Y-m-d` |
| `mode` | Selection mode | `single` (default), `multiple`, `range` |
| `multiple` | Enable multiple selection | Boolean |
| `min` | Earliest selectable date | `Y-m-d` or `"today"` |
| `max` | Latest selectable date | `Y-m-d` or `"today"` |
| `unavailable` | Disabled dates | Comma-separated `Y-m-d` |
| `size` | Calendar size | `xs`, `sm`, `base`, `lg`, `xl`, `2xl` |
| `months` | Number of months shown | Integer (default: 1 single/multiple, 2 range) |
| `min-range` | Min days in range mode | Integer |
| `max-range` | Max days in range mode | Integer |
| `start-day` | Week start day | `0` (Sunday) to `6` (Saturday) |
| `open-to` | Initial month/year | `Y-m-d` |
| `locale` | Calendar locale | e.g., `fr`, `en-US`, `ja-JP` |
| `static` | Non-interactive display | Boolean |
| `navigation` | Show month controls | Boolean (default: `true`) |
| `week-numbers` | Show week numbers | Boolean |
| `selectable-header` | Month/year dropdowns | Boolean |
| `with-today` | Today shortcut button | Boolean |
| `fixed-weeks` | Consistent week count | Boolean |

**Reference:** https://fluxui.dev/components/calendar
