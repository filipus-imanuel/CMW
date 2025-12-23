<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTerm extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'partner_address_id',
        'days',
        'description',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'integer',
        ];
    }

    public function partnerAddress(): BelongsTo
    {
        return $this->belongsTo(PartnerAddress::class);
    }
}
