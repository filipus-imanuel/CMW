<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaSetting extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'coa_id',
        'remarks',
    ];

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }
}
