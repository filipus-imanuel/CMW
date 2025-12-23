<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Uom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionConsumeDetail extends BaseModel
{
    protected $table = 'production_consume_details';

    protected $fillable = [
        'production_header_id',
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
        return $this->belongsTo(ProductionHeader::class, 'production_header_id');
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
