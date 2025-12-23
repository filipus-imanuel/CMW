<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Coa;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlJournalDetail extends BaseModel
{
    protected $table = 'gl_journal_details';

    protected $fillable = [
        'gl_journal_header_id',
        'coa_id',
        'debit',
        'credit',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(GlJournalHeader::class, 'gl_journal_header_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }
}
