<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentDetail extends BaseModel
{
    protected $table = 'stock_adjustment_details';

    protected $fillable = [
        'stock_adjustment_header_id',
        'item_id',
        'item_uom_id',
        'warehouse_id',
        'quantity_system',
        'quantity_actual',
        'quantity_difference',
    ];

    protected function casts(): array
    {
        return [
            'quantity_system' => 'decimal:2',
            'quantity_actual' => 'decimal:2',
            'quantity_difference' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(StockAdjustmentHeader::class, 'stock_adjustment_header_id');
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
