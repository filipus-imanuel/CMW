<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends BaseModel
{
    protected $fillable = [];

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
