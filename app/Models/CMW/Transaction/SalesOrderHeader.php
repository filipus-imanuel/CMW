<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrderHeader extends BaseModel
{
    protected $table = 'sales_order_headers';

    protected $fillable = [
        'date',
        'currency_id',
        'partner_id',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'subtotal' => 'decimal:5',
            'discount' => 'decimal:5',
            'tax' => 'decimal:5',
            'total' => 'decimal:5',
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

    public function details(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_header_id');
    }

    public function arInvoices(): HasMany
    {
        return $this->hasMany(ArInvoiceHeader::class, 'sales_order_header_id');
    }
}
