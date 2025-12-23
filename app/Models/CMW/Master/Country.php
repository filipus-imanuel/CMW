<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;

class Country extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'remarks',
    ];
}
