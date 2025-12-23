<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankGuaranteeOut extends BaseModel
{
    protected $table = 'bank_guarantee_outs';

    protected $fillable = [
        'code',
        'date',
        'due_date',
        'partner_id',
        'amount',
        'bank_name',
        'reference',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
