<?php

namespace App\Models;

use App\Models\CMW\BaseModel;
use App\Models\CMW\Master\Employee;
use App\Models\CMW\Transaction\SalesOrderDetail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCvSetting extends BaseModel
{
    protected $table = 'account_cv_settings';

    protected $fillable = [
        'category_id',
        'partner_id',
        'account_cv_id',
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

    public function accountCv(): BelongsTo
    {
        return $this->belongsTo(AccountCv::class);
    }
}
