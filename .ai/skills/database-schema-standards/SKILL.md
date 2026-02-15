# Database Schema & Model Standards

## Field Type Standards

| Field Purpose | Type | Max Length | Notes |
|---------------|------|------------|-------|
| `code` | `string` | 50 | Never enum |
| `name` | `string` | 100 | Standard names |
| `name` (long) | `string` | 255 | Longer names |
| `remarks` | `string` | 1024 | Never `text` |
| `description` (short) | `string` | 1024 | Brief descriptions |
| `description` (long) | `text` | - | Articles, HTML content only |

## Decimal Precision Standards

### Monetary Values (13, 2)
**Always use `decimal(13, 2)` for monetary amounts** - supports up to 99,999,999,999.99

Fields: `rate`, `price`, `amount`, `cost_price`, `sell_price`, `subtotal`, `discount`, `tax`, `total`, `paid`, `balance`, `unit_cost`, `debit`, `credit`, `quantity`, `min_stock`, `max_stock`, `quantity_in`, `quantity_out`, `quantity_system`, `quantity_actual`, `conversion_rate`

### Tax Rates (5, 2)
**Use `decimal(5, 2)` for percentage rates** - supports 0.00% to 999.99%

Fields: `rate` in taxes table

### NEVER Use Legacy Patterns
❌ `decimal(18, 5)`, `decimal(18, 4)`, `decimal(12, 5)`, `decimal(15, 2)`, `decimal(18, 6)`, `decimal(8, 4)`

## Model Casts

```php
protected function casts(): array
{
    return [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}
```

**Always use**:
- `'decimal:2'` for all numeric fields
- `'boolean'` for all boolean fields

## UI Formatting

```php
// Display formatted
{{ number_format($model->price, 2) }}

// Never format already formatted input
$validated = sanitize_numeric($request->input('price')); // This is raw
```

## Import Standards

**ALWAYS use `use` statements** after namespace - NEVER inline fully qualified class names (FQCN).

```php
// ❌ WRONG
class Create extends Component
{
    public function save()
    {
        try {
            $model = \App\Models\CMW\Master\Position::create([...]);
        } catch (\Illuminate\Database\QueryException $e) {
            // handle error
        }
    }
}

// ✅ CORRECT
use App\Models\CMW\Master\Position;
use Illuminate\Database\QueryException;
use Exception;

class Create extends Component
{
    public function save()
    {
        try {
            $model = Position::create([...]);
        } catch (QueryException $e) {
            // handle error
        }
    }
}
```

**Common exception imports**:
```php
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
```

## Model Fillable Standards

**Always check BaseModel first** - never duplicate inherited fillable fields.

BaseModel provides: `code`, `name`, `remarks`, `is_edit_locked`, `is_delete_locked`, `is_active`, `version_number`, `created_by`, `updated_by`, `deleted_by`

```php
// ❌ WRONG - Duplicating BaseModel fields
class Currency extends BaseModel
{
    protected $fillable = [
        'code',           // Already in BaseModel
        'name',           // Already in BaseModel
        'symbol',         // Currency-specific ✓
        'rate',           // Currency-specific ✓
        'remarks',        // Already in BaseModel
        'is_active',      // Already in BaseModel
    ];
}

// ✅ CORRECT - Only model-specific fields
class Currency extends BaseModel
{
    protected $fillable = [
        'symbol',
        'symbol_position',
        'rate',
    ];
}
```
