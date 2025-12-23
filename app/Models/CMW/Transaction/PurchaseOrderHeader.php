<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderHeader extends BaseModel
{
    protected $table = 'purchase_order_headers';

    protected $fillable = [
        'code',
        'date',
        'partner_id',
        'warehouse_id',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_header_id');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceiptHeader::class, 'purchase_order_header_id');
    }
}
