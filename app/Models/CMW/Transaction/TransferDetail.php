<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Uom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferDetail extends BaseModel
{
    protected $table = 'transfer_details';

    protected $fillable = [
        'transfer_header_id',
        'item_id',
        'uom_id',
        'quantity',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
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

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }
}
