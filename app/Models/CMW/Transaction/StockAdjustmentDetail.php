<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Item;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentDetail extends BaseModel
{
    protected $table = 'stock_adjustment_details';

    protected $fillable = [
        'stock_adjustment_header_id',
        'item_id',
        'uom_id',
        'warehouse_id',
        'quantity_system',
        'quantity_actual',
        'quantity_difference',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity_system' => 'decimal:4',
            'quantity_actual' => 'decimal:4',
            'quantity_difference' => 'decimal:4',
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

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
