<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'position_id',
        'email',
        'phone',
        'address',
        'remarks',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
