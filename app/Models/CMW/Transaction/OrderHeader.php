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
        'code_request',
        'code_order',
        'date',
        'delivery_date',
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
            'delivery_date' => 'date',
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

    public function deliveries(): HasMany
    {
        return $this->hasMany(DeliveryHeader::class, 'order_header_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnHeader::class, 'order_header_id');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeRequests(Builder $query): Builder
    {
        return $query->whereIn('status', ['INIT', 'APPROVAL']);
    }

    public function scopeInit(Builder $query): Builder
    {
        return $query->where('status', 'INIT');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'APPROVAL');
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->whereIn('status', ['ORDER', 'DELIVERY']);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'REJECTED');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'CANCELLED');
    }

    public function scopeOrders(Builder $query): Builder
    {
        return $query->whereIn('status', ['ORDER', 'DELIVERY', 'FINISH', 'FINAL']);
    }

    public function scopeReadyForDelivery(Builder $query): Builder
    {
        return $query->where('status', 'ORDER');
    }
}
