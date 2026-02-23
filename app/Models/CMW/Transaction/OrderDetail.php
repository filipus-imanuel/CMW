<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\CompanySetting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends BaseModel
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_header_id',
        'item_id',
        'item_uom_id',
        'company_setting_id',
        'quantity',
        'price_proposed',
        'price_deal',
        'discount',
        'tax',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price_proposed' => 'decimal:2',
            'price_deal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(OrderHeader::class, 'order_header_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }

    public function companySetting(): BelongsTo
    {
        return $this->belongsTo(CompanySetting::class, 'company_setting_id');
    }
}
