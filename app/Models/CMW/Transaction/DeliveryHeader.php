<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DeliveryHeader extends BaseModel
{
    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'delivery_headers';

    protected $fillable = [
        'date',
        'order_header_id',
        'partner_id',
        'company_id',
        'currency_id',
        'status',
        'cancel_reason',
        'confirmed_by',
        'confirmed_at',
        'subtotal',
        'tax',
        'total',
        'delivery_address',
        'vehicle_number',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'confirmed_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════════════════

    public function orderHeader(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class, 'order_header_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryDetail::class, 'delivery_header_id');
    }

    public function arInvoices(): HasMany
    {
        return $this->hasMany(ArInvoiceHeader::class, 'delivery_header_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnHeader::class, 'delivery_header_id');
    }

    public function inventoryLedgers(): MorphMany
    {
        return $this->morphMany(InventoryLedger::class, 'reference');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FINISHED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    public function isOngoing(): bool
    {
        return $this->status === self::STATUS_ONGOING;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
