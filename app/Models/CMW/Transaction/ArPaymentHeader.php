<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArPaymentHeader extends BaseModel
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'ar_payment_headers';

    protected $fillable = [
        'date',
        'currency_id',
        'partner_id',
        'company_id',
        'amount',
        'payment_method_id',
        'reference',
        'status',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ArPaymentDetail::class, 'ar_payment_header_id');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeActivePayments(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
