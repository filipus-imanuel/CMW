<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\History\HistoryItemPrice;
use App\Models\CMW\Master\BomHeader;
use App\Models\CMW\Master\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends BaseModel
{
    protected $fillable = [
        'type',
        'item_category_id',
        'currency_id',
        'cost_price',
        'sell_price',
        'min_stock',
        'max_stock',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'max_stock' => 'decimal:2',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function itemUoms(): HasMany
    {
        return $this->hasMany(ItemUom::class);
    }

    /**
     * Get the base UOM for this item (is_base = true).
     */
    public function baseItemUom(): HasOne
    {
        return $this->hasOne(ItemUom::class)->where('is_base', true);
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
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function itemPrices(): HasManyThrough
    {
        return $this->hasManyThrough(ItemPrice::class, ItemUom::class);
    }

    public function historyItemPrices(): HasManyThrough
    {
        return $this->hasManyThrough(HistoryItemPrice::class, ItemUom::class);
    }
}
