<?php

namespace App\Models\CMW\Master;

use App\Models\Category;
use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\InventoryLedger;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'uom_id',
        'cost_price',
        'sell_price',
        'min_stock',
        'max_stock',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'min_stock' => 'decimal:4',
            'max_stock' => 'decimal:4',
        ];
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
        return $this->belongsTo(Category::class);
    }
}
