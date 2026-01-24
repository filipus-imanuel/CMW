<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends BaseModel
{
    protected $fillable = [
        'department_id',
        'email',
        'phone',
        'address',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function companySetting(): HasMany
    {
        return $this->hasMany(CompanySetting::class, 'employee_id');
    }
}
