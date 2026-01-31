<?php

namespace App\Models\CMW\System;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'system_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'data_type',
        'name_id',
        'name_en',
        'name_ch',
        'description',
        'category',
    ];

    /**
     * Get the typed value based on data_type.
     */
    public function getValue(): mixed
    {
        return match ($this->data_type) {
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value, // string
        };
    }

    /**
     * Set the value with proper type handling.
     */
    public function setTypedValue(mixed $value): void
    {
        $this->value = match ($this->data_type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get the display name based on current locale.
     */
    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'id' => $this->name_id,
            'zh' => $this->name_ch,
            default => $this->name_en,
        };
    }

    /**
     * Get category from key (first segment).
     */
    public function getCategoryFromKeyAttribute(): string
    {
        return explode('.', $this->key)[0] ?? $this->category;
    }

    /**
     * Static helper to get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting?->getValue() ?? $default;
    }
}
