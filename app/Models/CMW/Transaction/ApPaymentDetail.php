<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApPaymentDetail extends BaseModel
{
    protected $table = 'ap_payment_details';

    protected $fillable = [
        'ap_payment_header_id',
        'ap_invoice_header_id',
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
        return $this->belongsTo(ApPaymentHeader::class, 'ap_payment_header_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ApInvoiceHeader::class, 'ap_invoice_header_id');
    }
}
