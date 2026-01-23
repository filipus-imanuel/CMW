<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTerm extends BaseModel
{
    protected $fillable = [
        'partner_address_id',
        'days',
        'description',
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
