<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockAdjustmentHeader extends BaseModel
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'stock_adjustment_headers';

    protected $fillable = [
        'date',
        'currency_id',
        'warehouse_id',
        'order_header_id',
        'work_order_auto',
        'work_order_manual',
        'production_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'production_date' => 'date',
            'is_active' => 'boolean',
            'is_edit_locked' => 'boolean',
            'is_delete_locked' => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function orderHeader(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_header_id');
    }

    public function inventoryLedgers(): MorphMany
    {
        return $this->morphMany(InventoryLedger::class, 'reference');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
