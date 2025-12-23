<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturnHeader extends BaseModel
{
    protected $table = 'purchase_return_headers';

    protected $fillable = [
        'code',
        'date',
        'partner_id',
        'warehouse_id',
        'goods_receipt_header_id',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptHeader::class, 'goods_receipt_header_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseReturnDetail::class, 'purchase_return_header_id');
    }
}
