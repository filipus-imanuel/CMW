<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlJournalHeader extends BaseModel
{
    protected $table = 'gl_journal_headers';

    protected $fillable = [
        'code',
        'date',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(GlJournalDetail::class, 'gl_journal_header_id');
    }
}
