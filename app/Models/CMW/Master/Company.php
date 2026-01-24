<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    protected $fillable = [
        'sales_limit',
        'currency_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'sales_limit' => 'decimal:2',
        ]);
    }

    /**
     * Get the currency that this company uses.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function itemCategories(): BelongsToMany
    {
        return $this->belongsToMany(ItemCategory::class, 'company_item_category')->withTimestamps();
    }
}
