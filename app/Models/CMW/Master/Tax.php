<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;

class Tax extends BaseModel
{
    protected $fillable = [
        'rate',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }
}
