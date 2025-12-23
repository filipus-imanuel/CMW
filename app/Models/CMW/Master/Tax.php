<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;

class Tax extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'rate',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
        ];
    }
}
