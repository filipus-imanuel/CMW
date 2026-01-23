<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends BaseModel
{
    protected $fillable = [
        'acquisition_date',
        'currency_id',
        'acquisition_cost',
        'current_value',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:5',
            'current_value' => 'decimal:5',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
