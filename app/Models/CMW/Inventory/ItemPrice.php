<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\History\HistoryItemPrice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ItemPrice extends BaseModel
{
    protected $table = 'item_prices';

    protected $fillable = [
        'item_uom_id',
        'category_price_id',
        'price',
        'remarks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }

    /**
     * Convenience: get the Item through ItemUom.
     */
    public function item(): HasOneThrough
    {
        return $this->hasOneThrough(
            Item::class,
            ItemUom::class,
            'id',           // item_uoms.id
            'id',           // items.id
            'item_uom_id',  // item_prices.item_uom_id
            'item_id'       // item_uoms.item_id
        );
    }

    public function categoryPrice(): BelongsTo
    {
        return $this->belongsTo(CategoryPrice::class);
    }

    public function historyItemPrices(): HasMany
    {
        return $this->hasMany(HistoryItemPrice::class, 'item_uom_id', 'item_uom_id')
            ->where('category_price_id', $this->category_price_id);
    }
}
