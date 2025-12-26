<?php

namespace App\Models;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCv extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'remarks',
    ];

    public function accountCvSettings(): HasMany
    {
        return $this->hasMany(AccountCvSetting::class);
    }
}
