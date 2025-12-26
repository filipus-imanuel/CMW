<?php

namespace App\Models;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
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

    public function accountCvSettings(): HasMany
    {
        return $this->hasMany(AccountCvSetting::class);
    }
}
