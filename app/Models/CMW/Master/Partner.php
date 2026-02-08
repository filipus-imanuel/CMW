<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends BaseModel
{
    protected $fillable = [
        'user_id',
        'is_supplier',
        'is_customer',
        'credit_limit',
    ];

    protected function casts(): array
    {
        return [
            'is_supplier' => 'boolean',
            'is_customer' => 'boolean',
            'credit_limit' => 'decimal:2',
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PartnerAddress::class);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeSuppliers(Builder $query): Builder
    {
        return $query->where('is_supplier', true);
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('is_customer', true);
    }

    public function scopeForSales(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->where('is_customer', true);
    }
}
