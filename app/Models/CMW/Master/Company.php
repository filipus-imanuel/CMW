<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Inventory\ItemCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    protected $fillable = [
        'sales_limit',
        'payment_code',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'currency_id',
        'tax_mode',
        'tax_id',
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

    /**
     * Get the default tax for this company.
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function companySettings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function itemCategories(): BelongsToMany
    {
        return $this->belongsToMany(ItemCategory::class, 'company_item_category')->withTimestamps();
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'company_warehouses')->withTimestamps();
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'company_partner')->withTimestamps();
    }
}
