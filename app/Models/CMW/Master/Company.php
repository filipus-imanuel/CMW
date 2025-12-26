<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'remarks',
    ];

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }
}
