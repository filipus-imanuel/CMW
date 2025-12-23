<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Uom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderDetail extends BaseModel
{
    protected $table = 'sales_order_details';

    protected $fillable = [
        'sales_order_header_id',
        'item_id',
        'uom_id',
        'quantity',
        'price',
        'discount',
        'tax',
        'total',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(SalesOrderHeader::class, 'sales_order_header_id');
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
