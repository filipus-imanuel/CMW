<?php

namespace App\Models\CMW;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    /**
     * Get the looping attribute for displaying in dropdowns.
     */
    public function getLoopingAttribute(): string
    {
        return $this->code.' - '.$this->name;
    }

    /**
     * Get the fillable attributes for the model.
     *
     * @return array<string>
     */
    public function getFillable(): array
    {
        return array_merge(parent::getFillable(), [
            'code',
            'name',
            'remarks',
            'is_edit_locked',
            'is_delete_locked',
            'is_active',
            'version_number',
            'created_by',
            'updated_by',
            'deleted_by',
        ]);
    }

    // Scopes

    public function scopeForLooping(Builder $query): Builder
    {
        return $query->select('id', 'code', 'name');
    }

    public function scopeOrderByCode(Builder $query): Builder
    {
        return $query->orderBy('code');
    }

    public function scopeActive(Builder $query, bool $status = true): Builder
    {
        return $query->where('is_active', $status);
    }

    // Relationships

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
