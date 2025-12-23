<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerAddress extends BaseModel
{
    protected $fillable = [
        'partner_id',
        'label',
        'address',
        'city',
        'phone',
        'contact_person',
        'is_default',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function creditTerms(): HasMany
    {
        return $this->hasMany(CreditTerm::class);
    }
}
