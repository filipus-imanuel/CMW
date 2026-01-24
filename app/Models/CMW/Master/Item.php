<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\InventoryLedger;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends BaseModel
{
    protected $fillable = [
        'type',
        'category_id',
        'uom_id',
        'currency_id',
        'cost_price',
        'sell_price',
        'min_stock',
        'max_stock',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:5',
            'sell_price' => 'decimal:5',
            'min_stock' => 'decimal:4',
            'max_stock' => 'decimal:4',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function bomHeaders(): HasMany
    {
        return $this->hasMany(BomHeader::class);
    }

    public function inventoryLedgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }
}
