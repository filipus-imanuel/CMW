<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Master\BomHeader;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionHeader extends BaseModel
{
    protected $table = 'production_headers';

    protected $fillable = [
        'date',
        'currency_id',
        'item_id',
        'bom_header_id',
        'warehouse_id',
        'quantity',
        'uom_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function consumeDetails(): HasMany
    {
        return $this->hasMany(ProductionConsumeDetail::class, 'production_header_id');
    }
}
