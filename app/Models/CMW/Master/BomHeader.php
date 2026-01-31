<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\Item;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BomHeader extends BaseModel
{
    protected $table = 'bom_headers';

    protected $fillable = [
        'item_id',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BomDetail::class, 'bom_header_id');
    }
}
