# Transactions & Data Integrity

## Transaction Wrapper Pattern

**ALWAYS wrap DB mutations in transactions**:

```php
use Illuminate\Support\Facades\{Auth, DB};
use App\Helpers\TransactionHelper;

public function store()
{
    $this->authorize('create entity');
    $validated = $this->validate();
    
    DB::transaction(function () use ($validated) {
        $entity = Entity::create([
            ...$validated['inputs'],
            'created_by' => Auth::id(),
        ]);
        
        // Lock and recalculate if financial aggregate
        if ($entity->hasFinancialImpact()) {
            $entity->lockForUpdate();
            TransactionHelper::updatePurchaseOrderHeaderTotals($entity);
        }
    });
    
    Flux::toast('Created successfully', variant: 'success', position: 'top-end');
}
```

## Lock for Financial Aggregates

Use `lockForUpdate()` when updating financial totals:

```php
DB::transaction(function () {
    $order = Order::findOrFail($orderId);
    $order->lockForUpdate();  // Prevent concurrent modifications
    
    $order->update([
        'total_amount' => $newTotal,
        'updated_by' => Auth::id(),
    ]);
    
    TransactionHelper::updatePurchaseOrderHeaderTotals($order);
});
```

## Never Trust Client Totals

**ALWAYS recalculate server-side** using TransactionHelper:

```php
// ❌ WRONG - trusting client
public function store()
{
    Order::create([
        'subtotal' => $this->inputs['subtotal'],  // From client!
        'tax' => $this->inputs['tax'],            // From client!
        'total' => $this->inputs['total'],        // From client!
    ]);
}

// ✅ CORRECT - recalculate server-side
public function store()
{
    $order = Order::create([
        'partner_id' => $this->inputs['partner_id'],
        // ... other non-calculated fields
    ]);
    
    // Server recalculates everything
    TransactionHelper::updatePurchaseOrderHeaderTotals($order);
}
```

## Totals Calculation Formula

**Formula**: `grand_total = total_amount - total_discount + tax_amount + total_cost`

### Tax IN (Inclusive)
Price already includes tax - normalize to base price:

```php
$basePrice = $priceWithTax / (1 + $taxRate);
$taxAmount = $priceWithTax - $basePrice;
```

### Tax EX (Exclusive)
Tax added on subtotal after discount:

```php
$subtotal = $quantity * $unitPrice;
$afterDiscount = $subtotal - $discount;
$taxAmount = $afterDiscount * $taxRate;
$total = $afterDiscount + $taxAmount;
```

## Sanitize Numeric Input

**Always use `sanitize_numeric()` on user input before arithmetic**:

```php
use function App\Helpers\sanitize_numeric;

public function calculateTotal()
{
    // ❌ WRONG - using raw input
    $total = $this->inputs['quantity'] * $this->inputs['price'];
    
    // ✅ CORRECT - sanitize first
    $quantity = sanitize_numeric($this->inputs['quantity']);
    $price = sanitize_numeric($this->inputs['price']);
    $total = $quantity * $price;
}
```

**Why**: User input might contain formatting characters (`,`, `space`, etc.) that break arithmetic.

## Audit Trail

**Always populate audit fields**:

```php
// Create
Entity::create([
    'name' => $validated['name'],
    'created_by' => Auth::id(),  // Required
]);

// Update
$entity->update([
    'name' => $validated['name'],
    'updated_by' => Auth::id(),  // Required
]);

// Soft Delete
$entity->update(['deleted_by' => Auth::id()]);  // Before delete()
$entity->delete();
```

## TransactionHelper Usage

**Don't recalculate manually** - use helper methods:

```php
use App\Helpers\TransactionHelper;

// After creating/updating order details
TransactionHelper::updatePurchaseOrderHeaderTotals($order);

// After creating/updating sales order details
TransactionHelper::updateSalesOrderHeaderTotals($order);
```

Helper ensures:
- Correct formula application
- Consistent rounding
- Tax calculation based on tax type (IN/EX)
- All related fields updated atomically
