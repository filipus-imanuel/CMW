# System Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The System module manages **application-wide configuration settings** via a key-value store. Settings are grouped by category and support multiple data types.

---

## 2. Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| Setting | `system_settings` | `App\Models\CMW\System\Setting` | Key-value configuration store |

---

## 3. Setting Model

### 3.1 Key Columns

| Column | Type | Purpose |
|--------|------|---------|
| `key` | string | Dotted key (e.g., `inventory.item_price.threshold_bypass_approval`) |
| `value` | string | Stored value (cast based on `data_type`) |
| `data_type` | string | Type: `string`, `integer`, `decimal`, `boolean`, `json` |
| `name_id` | string | Display name (Indonesian) |
| `name_en` | string | Display name (English) |
| `name_ch` | string | Display name (Chinese) |
| `description` | string | Description of the setting |
| `category` | string | Grouping category |

### 3.2 Type Casting

The `getValue()` method casts stored string values based on `data_type`:

| data_type | PHP Type |
|-----------|----------|
| `integer` | `int` |
| `decimal` | `float` |
| `boolean` | `bool` |
| `json` | `array` |
| `string` | `string` |

### 3.3 Multi-Language Display

`getDisplayNameAttribute()` returns the appropriate name based on current locale (`name_id`, `name_en`, `name_ch`).

---

## 4. Settings Edit (`System\Setting\Edit`)

### 4.1 Access

- **Permission**: `edit system setting`
- **Route**: `/cmw/system/settings`
- **Pattern**: Full-page form (not modal)

### 4.2 Behavior

- **Mount**: Loads all settings into `$inputs` array using `data_set()` for nested dotted key support (e.g., `inputs.inventory.item_price.threshold` maps to `$inputs['inventory']['item_price']['threshold']`)
- **Display**: Settings are grouped by first segment of the key via computed `groupedSettings()`
- **Update**: Iterates all settings, compares with current values, only updates changed settings. Boolean values are converted to `'1'`/`'0'` strings.
- **Transaction**: Uses `DB::transaction()` for atomicity
- **Audit**: Sets `updated_by` on each changed setting

---

## 5. Known Settings

Settings are seeded via database seeders. Common categories include:

| Category | Example Keys | Purpose |
|----------|-------------|---------|
| `inventory` | `inventory.item_price.threshold_bypass_approval` | Item price change approval threshold |
| `sales` | `sales.*` | Sales-related configuration |

> The exact setting keys depend on the seeder. Check `database/seeders/` for the current list.

---

## 6. Permission Matrix

| Permission | Actions |
|------------|---------|
| `system setting` | `edit` |

> Note: There is no `view` permission — the edit page doubles as the view page. Only users with `edit system setting` can access the settings page.

---

## 7. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `system.settings.edit` | `/cmw/system/settings` | `System\Setting\Edit` |

---

## 8. Related Files

| Area | Path |
|------|------|
| Setting Model | `app/Models/CMW/System/Setting.php` |
| Edit Component | `app/Livewire/System/Setting/Edit.php` |
| View | `resources/views/livewire/settings/system.blade.php` |
