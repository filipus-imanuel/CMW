<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\InventoryLedger;
use App\Models\CMW\Inventory\Item;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_warehouses')->withTimestamps();
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_warehouses')->withTimestamps();
    }
}
