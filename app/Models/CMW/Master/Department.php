<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends BaseModel
{
    protected $fillable = [];

    protected $table = 'departments';

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
