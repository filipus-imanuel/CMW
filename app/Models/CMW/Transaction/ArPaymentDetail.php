<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArPaymentDetail extends BaseModel
{
    protected $table = 'ar_payment_details';

    protected $fillable = [
        'ar_payment_header_id',
        'ar_invoice_header_id',
        'amount',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(ArPaymentHeader::class, 'ar_payment_header_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ArInvoiceHeader::class, 'ar_invoice_header_id');
    }
}
