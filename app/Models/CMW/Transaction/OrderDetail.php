<?php

namespace App\Models\CMW\Transaction;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Inventory\ItemUom;
use App\Models\CMW\Master\CompanySetting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDetail extends BaseModel
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_header_id',
        'item_id',
        'item_uom_id',
        'company_setting_id',
        'return_detail_id',
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

    public function returnDetail(): BelongsTo
    {
        return $this->belongsTo(ReturnDetail::class, 'return_detail_id');
    }

    public function deliveryDetails(): HasMany
    {
        return $this->hasMany(DeliveryDetail::class, 'order_detail_id');
    }

    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(OrderDeliverySchedule::class, 'order_detail_id');
    }
}
