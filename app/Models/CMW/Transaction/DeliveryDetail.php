<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryDetail extends BaseModel
{
    protected $table = 'delivery_details';

    protected $fillable = [
        'delivery_header_id',
        'order_detail_id',
        'item_id',
        'item_uom_id',
        'warehouse_id',
        'quantity_sent',
        'quantity_received',
        'price',
        'discount',
        'tax',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity_sent' => 'decimal:2',
            'quantity_received' => 'decimal:2',
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
        return $this->belongsTo(DeliveryHeader::class, 'delivery_header_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
