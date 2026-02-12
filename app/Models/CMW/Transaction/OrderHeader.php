<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\ItemCategory;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Tax;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderHeader extends BaseModel
{
    protected $table = 'order_headers';

    protected $fillable = [
        'date',
        'currency_id',
        'partner_id',
        'company_id',
        'item_category_id',
        'tax_mode',
        'tax_id',
        'tax_rate',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'subtotal',
        'discount',
        'tax',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════════════════

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
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

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_header_id');
    }

    public function arInvoices(): HasMany
    {
        return $this->hasMany(ArInvoiceHeader::class, 'order_header_id');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeRequests(Builder $query): Builder
    {
        return $query->whereIn('status', ['INIT', 'APPROVAL', 'REQUEST']);
    }

    public function scopeInit(Builder $query): Builder
    {
        return $query->where('status', 'INIT');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'APPROVAL');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'REQUEST');
    }

    public function scopeOrders(Builder $query): Builder
    {
        return $query->whereIn('status', ['ORDER', 'DELIVERY', 'FINISH', 'FINAL']);
    }
}
