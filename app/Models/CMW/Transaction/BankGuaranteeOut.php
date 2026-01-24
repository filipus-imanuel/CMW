<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankGuaranteeOut extends BaseModel
{
    protected $table = 'bank_guarantee_outs';

    protected $fillable = [
        'date',
        'due_date',
        'currency_id',
        'partner_id',
        'amount',
        'bank_name',
        'reference',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
