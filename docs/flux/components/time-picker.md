# Flux Time Picker Component

## Basic Usage
```blade
<!-- Simple time picker -->
<flux:time-picker />

<!-- With initial value (H:i format) -->
<flux:time-picker value="11:30" />

<!-- Livewire binding -->
<flux:time-picker wire:model="time" />
```

## Input Trigger
Attach time picker to a time input for precise selection:

```blade
<!-- Input type with dropdown -->
<flux:time-picker type="input" />

<!-- Input type without dropdown -->
<flux:time-picker type="input" :dropdown="false" />
```

## Multiple Times
```blade
<flux:time-picker multiple />
```

**Value format:** Single time `"11:30"` or multiple times `"11:30,14:00,16:30"`

## Time Format
```blade
<!-- Auto (uses browser locale, default) -->
<flux:time-picker time-format="auto" />

<!-- 12-hour format (e.g., 2:30 PM) -->
<flux:time-picker time-format="12-hour" />

<!-- 24-hour format (e.g., 14:30) -->
<flux:time-picker time-format="24-hour" />
```

## Interval Control
Set interval between displayed time options (default: 30 minutes):

```blade
<!-- 15-minute intervals -->
<flux:time-picker interval="15" />

<!-- 60-minute (hourly) intervals -->
<flux:time-picker interval="60" />
```

## Min/Max Times
Restrict selectable time range:

```blade
<!-- Business hours only -->
<flux:time-picker min="09:00" max="17:00" />

<!-- Prevent selection before now -->
<flux:time-picker min="now" />

<!-- Prevent selection after now -->
<flux:time-picker max="now" />
```

## Unavailable Times
Disable specific times from selection:

```blade
<!-- Block individual times and time ranges -->
<flux:time-picker unavailable="03:00,04:00,05:30-07:29" />
```

## Open To
Set initial display time:

```blade
<flux:time-picker open-to="10:00" />
```

Default: selected time, or current time if none selected.

## With Label and Description
```blade
<flux:time-picker 
    label="Appointment time" 
    description="Select your preferred time slot"
    badge="Required"
/>

<!-- Description below input -->
<flux:time-picker 
    label="Appointment time" 
    description:trailing="Business hours only"
/>
```

## Additional Features
```blade
<!-- Placeholder text -->
<flux:time-picker placeholder="Select a time" />

<!-- Clearable button -->
<flux:time-picker clearable />

<!-- Disabled state -->
<flux:time-picker disabled />

<!-- Error state -->
<flux:time-picker invalid />

<!-- Size variants -->
<flux:time-picker size="sm" />
<flux:time-picker size="xs" />
```

## Localization
```blade
<!-- Auto (uses browser locale, default) -->
<flux:time-picker locale="auto" />

<!-- Specific locale -->
<flux:time-picker locale="ja-JP" />
<flux:time-picker locale="fr" />
<flux:time-picker locale="en-US" />
```

## Component Reference

### `<flux:time-picker>`
**Props:**
- `wire:model` - Binds to Livewire property
- `value` - Selected time(s) in `H:i` format (single) or `H:i,H:i` (multiple)
- `type` - Picker type: `button` (default), `input`
- `multiple` - Allow multiple time selection (default: `false`)
- `time-format` - Format: `auto` (default), `12-hour`, `24-hour`
- `interval` - Minutes between options (default: `30`)
- `min` - Earliest selectable time (time string or `"now"`)
- `max` - Latest selectable time (time string or `"now"`)
- `unavailable` - Comma-separated unavailable times/ranges (e.g., `"03:00,05:30-07:29"`)
- `open-to` - Initial display time (default: selected time or now)
- `label` - Label text (auto-wraps in `flux:field`)
- `description` - Help text above picker
- `description:trailing` - Help text below picker
- `badge` - Badge text at end of label
- `placeholder` - Placeholder text when empty
- `size` - Size: `sm`, `xs`
- `clearable` - Shows clear button when time selected
- `disabled` - Prevents interaction
- `invalid` - Applies error styling
- `locale` - Locale string (e.g., `fr`, `en-US`, `ja-JP`)
- `dropdown` - Show/hide dropdown (for `type="input"` only)

**Attributes:**
- `data-flux-time-picker` - Applied for styling and identification

## Usage Guidelines
- Use `type="input"` for precise time entry combined with dropdown selection
- Set appropriate `interval` based on use case (15min for appointments, 60min for general scheduling)
- Use `min`/`max` to enforce business hours or valid time ranges
- Mark unavailable times for appointment booking systems
- Use `clearable` for optional time fields
- Combine with date picker for complete datetime selection

**Reference:** https://fluxui.dev/components/time-picker
