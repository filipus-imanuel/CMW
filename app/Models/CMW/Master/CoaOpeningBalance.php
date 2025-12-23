<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaOpeningBalance extends BaseModel
{
    protected $fillable = [
        'coa_id',
        'period',
        'debit',
        'credit',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }
}
