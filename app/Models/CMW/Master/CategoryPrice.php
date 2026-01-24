<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryPrice extends BaseModel
{
    protected $table = 'category_prices';

    protected $fillable = [];

    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }
}
