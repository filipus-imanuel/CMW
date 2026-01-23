<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApInvoiceHeader extends BaseModel
{
    protected $table = 'ap_invoice_headers';

    protected $fillable = [
        'date',
        'due_date',
        'currency_id',
        'partner_id',
        'goods_receipt_header_id',
        'subtotal',
        'tax',
        'total',
        'paid',
        'balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:5',
            'tax' => 'decimal:5',
            'total' => 'decimal:5',
            'paid' => 'decimal:5',
            'balance' => 'decimal:5',
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

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptHeader::class, 'goods_receipt_header_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(ApPaymentDetail::class, 'ap_invoice_header_id');
    }
}
