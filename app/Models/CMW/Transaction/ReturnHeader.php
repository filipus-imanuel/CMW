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

class ReturnHeader extends BaseModel
{
    public const STATUS_INIT = 'INIT';

    public const STATUS_APPROVAL = 'APPROVAL';

    public const STATUS_PROCESSING = 'PROCESSING';

    public const STATUS_FINISH = 'FINISH';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUS_REJECTED = 'REJECTED';

    public const TYPE_ITEM = 'ITEM';

    public const TYPE_INVOICE_RETURN = 'INVOICE_RETURN';

    public const TYPE_INVOICE_DISCARD = 'INVOICE_DISCARD';

    public const TYPE_ITEM_INVOICE = 'ITEM_INVOICE';

    protected $table = 'return_headers';

    protected $fillable = [
        'transaction_type',
        'return_type',
        'date',
        'order_header_id',
        'delivery_header_id',
        'ar_invoice_header_id',
        'partner_id',
        'company_id',
        'currency_id',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'subtotal',
        'tax',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
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

    public function deliveryHeader(): BelongsTo
    {
        return $this->belongsTo(DeliveryHeader::class, 'delivery_header_id');
    }

    public function arInvoice(): BelongsTo
    {
        return $this->belongsTo(ArInvoiceHeader::class, 'ar_invoice_header_id');
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

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReturnDetail::class, 'return_header_id');
    }

    public function inventoryLedgers(): MorphMany
    {
        return $this->morphMany(InventoryLedger::class, 'reference');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════════════════

    public function scopeSalesOrder(Builder $query): Builder
    {
        return $query->where('transaction_type', 'SO');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INIT);
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVAL);
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FINISH);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeWarehousePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING)
            ->whereNull('received_at');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    public function isInit(): bool
    {
        return $this->status === self::STATUS_INIT;
    }

    public function isApproval(): bool
    {
        return $this->status === self::STATUS_APPROVAL;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isFinish(): bool
    {
        return $this->status === self::STATUS_FINISH;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isWarehouseReceived(): bool
    {
        return $this->received_at !== null;
    }

    public function isItemType(): bool
    {
        return $this->return_type === self::TYPE_ITEM;
    }

    public function isInvoiceReturn(): bool
    {
        return $this->return_type === self::TYPE_INVOICE_RETURN;
    }

    public function isInvoiceDiscard(): bool
    {
        return $this->return_type === self::TYPE_INVOICE_DISCARD;
    }

    public function isItemInvoice(): bool
    {
        return $this->return_type === self::TYPE_ITEM_INVOICE;
    }

    public function isInvoiceType(): bool
    {
        return in_array($this->return_type, [self::TYPE_INVOICE_RETURN, self::TYPE_INVOICE_DISCARD]);
    }

    public function isAllocationType(): bool
    {
        return in_array($this->return_type, [self::TYPE_ITEM, self::TYPE_ITEM_INVOICE]);
    }

    public static function returnTypeBadgeColor(string $type): string
    {
        return match ($type) {
            self::TYPE_ITEM => 'blue',
            self::TYPE_ITEM_INVOICE => 'teal',
            self::TYPE_INVOICE_RETURN => 'purple',
            self::TYPE_INVOICE_DISCARD => 'amber',
            default => 'zinc',
        };
    }

    public static function returnTypeHtmlBadge(string $type): string
    {
        $colorClass = match ($type) {
            self::TYPE_ITEM => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::TYPE_ITEM_INVOICE => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200',
            self::TYPE_INVOICE_RETURN => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            self::TYPE_INVOICE_DISCARD => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200',
        };

        return '<span class="px-2 py-1 text-xs font-medium rounded '.$colorClass.'">'.$type.'</span>';
    }
}
