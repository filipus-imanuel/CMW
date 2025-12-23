<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArInvoiceHeader extends BaseModel
{
    protected $table = 'ar_invoice_headers';

    protected $fillable = [
        'code',
        'date',
        'due_date',
        'partner_id',
        'sales_order_header_id',
        'subtotal',
        'tax',
        'total',
        'paid',
        'balance',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrderHeader::class, 'sales_order_header_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(ArPaymentDetail::class, 'ar_invoice_header_id');
    }
}
