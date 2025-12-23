<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uom extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'remarks',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function fromConversions(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'from_uom_id');
    }

    public function toConversions(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'to_uom_id');
    }
}
