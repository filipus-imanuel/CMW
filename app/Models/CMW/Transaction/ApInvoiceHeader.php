<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApInvoiceHeader extends BaseModel
{
    protected $table = 'ap_invoice_headers';

    protected $fillable = [
        'code',
        'date',
        'due_date',
        'partner_id',
        'goods_receipt_header_id',
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

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptHeader::class, 'goods_receipt_header_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(ApPaymentDetail::class, 'ap_invoice_header_id');
    }
}
