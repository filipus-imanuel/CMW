<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApPaymentHeader extends BaseModel
{
    protected $table = 'ap_payment_headers';

    protected $fillable = [
        'code',
        'date',
        'partner_id',
        'amount',
        'payment_method',
        'reference',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ApPaymentDetail::class, 'ap_payment_header_id');
    }
}
