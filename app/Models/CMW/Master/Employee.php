<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends BaseModel
{
    protected $fillable = [
        'position_id',
        'email',
        'phone',
        'address',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function companySetting(): HasMany
    {
        return $this->hasMany(CompanySetting::class, 'employee_id');
    }
}
