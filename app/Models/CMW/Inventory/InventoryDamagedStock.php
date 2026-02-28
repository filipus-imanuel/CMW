<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryDamagedStock extends BaseModel
{
    protected $table = 'inventory_damaged_stocks';

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'item_uom_id',
        'quantity',
        'date',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
