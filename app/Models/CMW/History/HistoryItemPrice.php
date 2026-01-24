<?php

namespace App\Models\CMW\History;

use App\Models\CMW\Master\CategoryPrice;
use App\Models\CMW\Master\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryItemPrice extends Model
{
    protected $table = 'history_item_prices';

    protected $fillable = [
        'item_id',
        'category_price_id',
        'old_price',
        'new_price',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'old_price' => 'decimal:5',
            'new_price' => 'decimal:5',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate the price difference (new - old).
     */
    public function getPriceDifferenceAttribute(): float
    {
        return (float) $this->new_price - (float) $this->old_price;
    }

    /**
     * Calculate the percentage change.
     */
    public function getPercentageChangeAttribute(): ?float
    {
        if ((float) $this->old_price === 0.0) {
            return null;
        }

        return (($this->new_price - $this->old_price) / $this->old_price) * 100;
    }
}
