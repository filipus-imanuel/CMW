<?php

declare(strict_types=1);

namespace App\Helpers\CMW\Validation;

/**
 * CurrencyValidationHelper - Centralized validation rules for currency-related fields.
 *
 * Provides reusable validation rules for currency_id fields, monetary amounts,
 * and exchange rates across all Livewire components.
 *
 * @example Basic usage
 * ```php
 * // In Create component
 * public function rules(): array
 * {
 *     return [
 *         'inputs.currency_id' => CurrencyValidationHelper::currencyIdRules(),
 *         'inputs.amount' => CurrencyValidationHelper::amountRules(),
 *     ];
 * }
 *
 * // In Edit component (prevent currency change)
 * public function rules(): array
 * {
 *     return [
 *         'inputs.currency_id' => CurrencyValidationHelper::currencyIdEditRules($this->model->currency_id),
 *     ];
 * }
 * ```
 */
class CurrencyValidationHelper
{
    /**
     * Get validation rules for currency_id in Create components.
     * Ensures only active currencies can be selected.
     */
    public static function currencyIdRules(): string
    {
        return 'required|exists:currencies,id,is_active,1';
    }

    /**
     * Get validation rules for currency_id in Edit components.
     * Prevents currency changes after transaction is created.
     *
     * @param  int  $currentCurrencyId  The current currency_id of the model
     */
    public static function currencyIdEditRules(int $currentCurrencyId): string
    {
        return 'required|in:'.$currentCurrencyId;
    }

    /**
     * Get validation rules for nullable currency_id (for master data).
     * Allows null or any active currency.
     */
    public static function currencyIdNullableRules(): string
    {
        return 'nullable|exists:currencies,id,is_active,1';
    }

    /**
     * Get validation rules for monetary amount fields.
     *
     * @param  float  $min  Minimum allowed value (default: 0)
     * @param  float  $max  Maximum allowed value (default: 9999999999999 - 13 digits for decimal(18,5))
     */
    public static function amountRules(float $min = 0, float $max = 9999999999999): string
    {
        return "required|numeric|min:{$min}|max:{$max}";
    }

    /**
     * Get validation rules for nullable monetary amount fields.
     *
     * @param  float  $min  Minimum allowed value (default: 0)
     * @param  float  $max  Maximum allowed value (default: 9999999999999 - 13 digits for decimal(18,5))
     */
    public static function amountNullableRules(float $min = 0, float $max = 9999999999999): string
    {
        return "nullable|numeric|min:{$min}|max:{$max}";
    }

    /**
     * Get validation rules for exchange rate fields.
     */
    public static function exchangeRateRules(): string
    {
        return 'required|numeric|min:0.00001|max:9999999999999';
    }

    /**
     * Get custom error messages for currency validation.
     *
     * @return array<string, string>
     */
    public static function currencyMessages(): array
    {
        return [
            'inputs.currency_id.required' => 'Currency is required.',
            'inputs.currency_id.exists' => 'Selected currency is invalid or inactive.',
            'inputs.currency_id.in' => 'Currency cannot be changed after transaction is created.',
        ];
    }

    /**
     * Get custom error messages for amount validation.
     *
     * @return array<string, string>
     */
    public static function amountMessages(): array
    {
        return [
            'inputs.amount.required' => 'Amount is required.',
            'inputs.amount.numeric' => 'Amount must be a number.',
            'inputs.amount.min' => 'Amount must be at least :min.',
            'inputs.amount.max' => 'Amount cannot exceed :max.',
        ];
    }

    /**
     * Get custom error messages for exchange rate validation.
     *
     * @return array<string, string>
     */
    public static function exchangeRateMessages(): array
    {
        return [
            'inputs.rate.required' => 'Exchange rate is required.',
            'inputs.rate.numeric' => 'Exchange rate must be a number.',
            'inputs.rate.min' => 'Exchange rate must be greater than 0.',
            'inputs.rate.max' => 'Exchange rate is too large.',
        ];
    }
}
