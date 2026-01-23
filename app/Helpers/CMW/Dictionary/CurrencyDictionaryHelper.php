<?php

declare(strict_types=1);

namespace App\Helpers\CMW\Dictionary;

/**
 * CurrencyDictionaryHelper - Static dictionaries for currency-related dropdown options.
 *
 * Provides reusable arrays of value-label pairs for currency select components
 * where data is static and doesn't come from database tables.
 *
 * @example Basic usage
 * ```php
 * // Get symbol position options
 * $positions = CurrencyDictionaryHelper::getSymbolPositions();
 *
 * // Get valid values for validation
 * $validValues = CurrencyDictionaryHelper::getSymbolPositionValues();
 *
 * // Get label for a specific value
 * $label = CurrencyDictionaryHelper::getSymbolPositionLabel('BEFORE');
 * ```
 */
class CurrencyDictionaryHelper
{
    // ══════════════════════════════════════════════════════════════════════════
    // SYMBOL POSITION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get symbol position options for currency display.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function getSymbolPositions(): array
    {
        return [
            ['value' => 'BEFORE', 'label' => 'Before Amount'],
            ['value' => 'AFTER', 'label' => 'After Amount'],
        ];
    }

    /**
     * Get all valid symbol position values (for validation).
     *
     * @return array<int, string>
     */
    public static function getSymbolPositionValues(): array
    {
        return array_column(self::getSymbolPositions(), 'value');
    }

    /**
     * Get formatted label for a given symbol position value.
     */
    public static function getSymbolPositionLabel(string $value): ?string
    {
        $positions = self::getSymbolPositions();
        $found = array_filter($positions, fn ($position) => $position['value'] === $value);

        return ! empty($found) ? array_values($found)[0]['label'] : null;
    }

    /**
     * Get symbol position values as comma-separated string for validation rules.
     */
    public static function getSymbolPositionValidationString(): string
    {
        return implode(',', self::getSymbolPositionValues());
    }
}
