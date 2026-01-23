<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\InventoryLedger;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends BaseModel
{
    protected $fillable = [
        'address',
    ];

    public function inventoryLedgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }
}
