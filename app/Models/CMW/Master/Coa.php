<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Coa extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'remarks',
    ];

    public function setting(): HasOne
    {
        return $this->hasOne(CoaSetting::class);
    }

    public function openingBalances(): HasMany
    {
        return $this->hasMany(CoaOpeningBalance::class);
    }
}
