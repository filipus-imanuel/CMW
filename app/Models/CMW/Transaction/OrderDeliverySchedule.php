<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliverySchedule extends BaseModel
{
    protected $table = 'order_delivery_schedules';

    protected $fillable = [
        'order_header_id',
        'order_detail_id',
        'delivery_date',
        'quantity',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'delivery_date' => 'date',
            'quantity' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class, 'order_header_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
}
