<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends BaseModel
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

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }
}
