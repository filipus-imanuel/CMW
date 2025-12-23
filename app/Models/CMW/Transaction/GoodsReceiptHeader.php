<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptHeader extends BaseModel
{
    protected $table = 'goods_receipt_headers';

    protected $fillable = [
        'code',
        'date',
        'partner_id',
        'warehouse_id',
        'purchase_order_header_id',
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

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderHeader::class, 'purchase_order_header_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class, 'goods_receipt_header_id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturnHeader::class, 'goods_receipt_header_id');
    }

    public function apInvoices(): HasMany
    {
        return $this->hasMany(ApInvoiceHeader::class, 'goods_receipt_header_id');
    }
}
