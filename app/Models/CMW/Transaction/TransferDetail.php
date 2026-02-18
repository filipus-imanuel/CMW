<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferDetail extends BaseModel
{
    protected $table = 'transfer_details';

    protected $fillable = [
        'transfer_header_id',
        'item_id',
        'item_uom_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(TransferHeader::class, 'transfer_header_id');
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
