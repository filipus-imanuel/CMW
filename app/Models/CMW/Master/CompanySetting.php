<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Transaction\SalesOrderDetail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySetting extends BaseModel
{
    protected $table = 'company_settings';

    protected $fillable = [
        'category_id',
        'partner_id',
        'company_id',
    ];

    public function salesOrderDetails(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
