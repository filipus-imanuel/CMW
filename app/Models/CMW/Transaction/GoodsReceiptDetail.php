<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Uom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDetail extends BaseModel
{
    protected $table = 'goods_receipt_details';

    protected $fillable = [
        'goods_receipt_header_id',
        'item_id',
        'uom_id',
        'quantity',
        'price',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'price' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptHeader::class, 'goods_receipt_header_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }
}
