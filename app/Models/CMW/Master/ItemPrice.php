<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\History\HistoryItemPrice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemPrice extends BaseModel
{
    protected $table = 'item_prices';

    protected $fillable = [
        'item_id',
        'category_price_id',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:5',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function categoryPrice(): BelongsTo
    {
        return $this->belongsTo(CategoryPrice::class);
    }

    public function historyItemPrices(): HasMany
    {
        return $this->hasMany(HistoryItemPrice::class, 'item_id', 'item_id')
            ->where('category_price_id', $this->category_price_id);
    }
}
