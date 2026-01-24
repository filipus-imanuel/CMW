<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryLedger extends BaseModel
{
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'date',
        'type',
        'reference_type',
        'reference_id',
        'quantity_in',
        'quantity_out',
        'balance',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity_in' => 'decimal:2',
            'quantity_out' => 'decimal:2',
            'balance' => 'decimal:2',
            'unit_cost' => 'decimal:2',
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

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
