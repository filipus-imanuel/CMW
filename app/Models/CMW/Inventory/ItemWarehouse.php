<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemWarehouse extends BaseModel
{
    protected $table = 'item_warehouses';

    protected $fillable = [
        'item_id',
        'warehouse_id',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
