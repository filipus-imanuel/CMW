<?php

namespace App\Models\CMW\Master;

use App\Models\AccountCvSetting;
use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function accountCvSetting(): HasMany
    {
        return $this->hasMany(AccountCvSetting::class, 'employee_id');
    }
}
