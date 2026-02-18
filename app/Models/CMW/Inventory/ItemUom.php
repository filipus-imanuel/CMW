<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Uom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemUom extends BaseModel
{
    protected $table = 'item_uoms';

    protected $fillable = [
        'item_id',
        'uom_id',
        'conversion_rate',
        'is_base',
    ];

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'conversion_rate' => 'decimal:4',
            'is_base' => 'boolean',
        ]);
    }

    // Relationships

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    /**
     * Get the UOM code via the related Uom model.
     */
    public function getUomCodeAttribute(): ?string
    {
        return $this->uom?->code;
    }

    /**
     * Get the UOM name via the related Uom model.
     */
    public function getUomNameAttribute(): ?string
    {
        return $this->uom?->name;
    }
}
