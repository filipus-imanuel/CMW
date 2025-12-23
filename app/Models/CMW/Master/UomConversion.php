<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UomConversion extends BaseModel
{
    protected $fillable = [
        'from_uom_id',
        'to_uom_id',
        'conversion_rate',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'conversion_rate' => 'decimal:6',
        ];
    }

    public function fromUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'from_uom_id');
    }

    public function toUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'to_uom_id');
    }
}
