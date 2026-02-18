<?php

namespace App\Models\CMW\Inventory;

use App\Models\CMW\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingItemPrice extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'item_prices_pending';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'item_price_id',
        'item_uom_id',
        'category_price_id',
        'old_price',
        'new_price',
        'change_percentage',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'reviewed_at',
        'approval_notes',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'old_price' => 'decimal:2',
            'new_price' => 'decimal:2',
            'change_percentage' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'processed_at' => 'datetime',
        ]);
    }

    // Scopes

    /**
     * Scope to filter pending approvals.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter approved records.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter rejected records.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // Accessors

    /**
     * Get formatted change percentage with +/- sign.
     */
    public function getChangePercentageFormattedAttribute(): string
    {
        $sign = $this->change_percentage >= 0 ? '+' : '';

        return $sign.number_format($this->change_percentage, 2).'%';
    }

    // Relationships

    /**
     * Get the item price that this pending record belongs to.
     */
    public function itemPrice(): BelongsTo
    {
        return $this->belongsTo(ItemPrice::class);
    }

    /**
     * Get the item UOM associated with this pending record.
     */
    public function itemUom(): BelongsTo
    {
        return $this->belongsTo(ItemUom::class);
    }

    /**
     * Get the category price associated with this pending record.
     */
    public function categoryPrice(): BelongsTo
    {
        return $this->belongsTo(CategoryPrice::class);
    }

    /**
     * Get the user who submitted this pending record.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who approved/rejected this pending record.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
