<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnDetail extends BaseModel
{
    protected $table = 'purchase_return_details';

    protected $fillable = [
        'purchase_return_header_id',
        'item_id',
        'item_uom_id',
        'quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturnHeader::class, 'purchase_return_header_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }
}
