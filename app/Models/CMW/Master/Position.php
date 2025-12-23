<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'remarks',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
