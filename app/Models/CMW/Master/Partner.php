<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends BaseModel
{
    protected $fillable = [
        'is_supplier',
        'is_customer',
    ];

    protected function casts(): array
    {
        return [
            'is_supplier' => 'boolean',
            'is_customer' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PartnerAddress::class);
    }

    public function scopeSuppliers($query)
    {
        return $query->where('is_supplier', true);
    }

    public function scopeCustomers($query)
    {
        return $query->where('is_customer', true);
    }
}
