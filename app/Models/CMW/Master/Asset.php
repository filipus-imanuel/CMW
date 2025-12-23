<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;

class Asset extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'acquisition_date',
        'acquisition_cost',
        'current_value',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'current_value' => 'decimal:2',
        ];
    }
}
