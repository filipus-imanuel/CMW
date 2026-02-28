<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDetail extends BaseModel
{
    protected $table = 'return_details';

    protected $fillable = [
        'return_header_id',
        'delivery_detail_id',
        'item_id',
        'item_uom_id',
        'quantity_return',
        'quantity_received_good',
        'quantity_received_damaged',
        'quantity_redelivery',
        'quantity_next_so',
        'is_next_so_consumed',
        'consumed_by_order_id',
        'price',
        'discount',
        'tax',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity_return' => 'decimal:2',
            'quantity_received_good' => 'decimal:2',
            'quantity_received_damaged' => 'decimal:2',
            'quantity_redelivery' => 'decimal:2',
            'quantity_next_so' => 'decimal:2',
            'is_next_so_consumed' => 'boolean',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════════════════

    public function header(): BelongsTo
    {
        return $this->belongsTo(ReturnHeader::class, 'return_header_id');
    }

    public function deliveryDetail(): BelongsTo
    {
        return $this->belongsTo(DeliveryDetail::class, 'delivery_detail_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }

    public function consumedByOrder(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class, 'consumed_by_order_id');
    }
}
