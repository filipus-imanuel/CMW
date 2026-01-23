<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlJournalHeader extends BaseModel
{
    protected $table = 'gl_journal_headers';

    protected $fillable = [
        'date',
        'currency_id',
        'description',
        'total_debit',
        'total_credit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_debit' => 'decimal:5',
            'total_credit' => 'decimal:5',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(GlJournalDetail::class, 'gl_journal_header_id');
    }
}
