<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\CompanySetting;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends BaseModel
{
    protected $table = 'item_categories';

    protected $fillable = [];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_item_category')->withTimestamps();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'item_category_id');
    }

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanySetting::class, 'item_category_id');
    }
}
