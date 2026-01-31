<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

use App\Models\CMW\Inventory\CategoryPrice;
use App\Models\CMW\Inventory\Item;
use App\Models\CMW\Master\Company;
use App\Models\CMW\Master\Country;
use App\Models\CMW\Master\CreditTerm;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Department;
use App\Models\CMW\Master\Employee;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Master\PartnerAddress;
use App\Models\CMW\Master\Tax;
use App\Models\CMW\Master\Uom;
use App\Models\CMW\Master\UserGroup;
use App\Models\CMW\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * PopulateDataHelper - Generic helper for populating dropdown/select data.
 *
 * This helper provides a centralized way to fetch and format data for dropdown/select components
 * across the application. It replaces repetitive load{Entity}() methods in Livewire components.
 *
 * @example Basic usage
 * ```php
 * // Simple usage with defaults (code - name format, active records only)
 * $warehouses = PopulateDataHelper::get(Warehouse::class);
 *
 * // With custom filters
 * $suppliers = PopulateDataHelper::get(Partner::class, [
 *     'filters' => ['is_supplier' => true],
 * ]);
 *
 * // With custom label format
 * $taxes = PopulateDataHelper::get(Tax::class, [
 *     'labelFormat' => fn($item) => "{$item->name} - {$item->rate}%",
 * ]);
 *
 * // With eager loading
 * $addresses = PopulateDataHelper::get(PartnerAddress::class, [
 *     'with' => ['partner'],
 *     'labelFormat' => fn($item) => "{$item->partner->name} - {$item->label}",
 * ]);
 *
 * // With default option prepended
 * $positions = PopulateDataHelper::get(Position::class, [
 *     'prependDefault' => true,
 *     'defaultLabel' => 'Select Position',
 * ]);
 * ```
 * @example Convenience methods
 * ```php
 * // Use shorthand methods for common entities
 * $departments = PopulateDataHelper::getDepartments();
 * $suppliers = PopulateDataHelper::getSuppliers();
 * $customers = PopulateDataHelper::getCustomers();
 * ```
 */
class PopulateDataHelper
{
    /**
     * Default cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Cache key prefix.
     */
    private const CACHE_PREFIX = 'popdata';

    // ══════════════════════════════════════════════════════════════════════════
    // GENERIC METHOD
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get dropdown data for any model.
     *
     * @param  string  $modelClass  Fully qualified model class name (e.g., Warehouse::class)
     * @param  array<string, mixed>  $options  Configuration options:
     *                                         - valueField (string): Field to use as value, default: 'id'
     *                                         - labelFormat (string|callable): Label format - 'name', 'name_code', 'code_name', or Closure, default: 'code_name'
     *                                         - orderBy (string): Field to order by, default: 'name'
     *                                         - orderDirection (string): Order direction 'asc' or 'desc', default: 'asc'
     *                                         - filters (array): Additional where clauses, default: []
     *                                         - with (array): Relationships to eager load, default: []
     *                                         - includeInactive (bool): Include is_active = false records, default: false
     *                                         - prependDefault (bool): Add default option at beginning, default: false
     *                                         - defaultLabel (string): Label for default option, default: "Select {entity}"
     *                                         - defaultValue (mixed): Value for default option, default: ''
     *                                         - useCache (bool): Enable caching, default: true
     *                                         - cacheTtl (int): Cache TTL in seconds, default: 3600
     * @return array<int, array{value: mixed, label: string}> Array of value-label pairs
     */
    public static function get(string $modelClass, array $options = []): array
    {
        // Merge with defaults
        $options = array_merge([
            'valueField' => 'id',
            'labelFormat' => 'code_name', // Default: "code - name"
            'orderBy' => 'name',
            'orderDirection' => 'asc',
            'filters' => [],
            'with' => [],
            'includeInactive' => false,
            'prependDefault' => false,
            'defaultLabel' => null,
            'defaultValue' => '',
            'useCache' => true,
            'cacheTtl' => self::CACHE_TTL,
        ], $options);

        // Generate cache key
        $cacheKey = self::generateCacheKey($modelClass, $options);

        // Return cached data if enabled
        if ($options['useCache']) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Build query
        $query = $modelClass::query();

        // Apply active filter (unless explicitly disabled)
        if (! $options['includeInactive']) {
            $query->where('is_active', true);
        }

        // Apply custom filters
        foreach ($options['filters'] as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        // Apply eager loading
        if (! empty($options['with'])) {
            $query->with($options['with']);
        }

        // Apply ordering
        $query->orderBy($options['orderBy'], $options['orderDirection']);

        // Get results
        $results = $query->get();

        // Map to value-label format
        $data = $results->map(function ($item) use ($options) {
            return [
                'value' => $item->{$options['valueField']},
                'label' => self::formatLabel($item, $options['labelFormat']),
            ];
        })->toArray();

        // Prepend default option if requested
        if ($options['prependDefault']) {
            $defaultLabel = $options['defaultLabel'] ?? self::generateDefaultLabel($modelClass);
            array_unshift($data, [
                'value' => $options['defaultValue'],
                'label' => $defaultLabel,
            ]);
        }

        // Cache the results
        if ($options['useCache']) {
            Cache::put($cacheKey, $data, $options['cacheTtl']);
        }

        return $data;
    }

    /**
     * Clear cache for a specific model or all populate data cache.
     *
     * @param  string|null  $modelClass  Model class to clear cache for, or null to clear all
     */
    public static function clearCache(?string $modelClass = null): void
    {
        if ($modelClass === null) {
            // Clear all popdata cache
            Cache::flush();
        } else {
            // Clear all cache keys for this model by iterating possible variations
            $modelName = class_basename($modelClass);
            $prefix = self::CACHE_PREFIX.'.'.$modelName.'.';

            // Since we can't easily iterate cache keys without Redis tags,
            // we'll use a pragmatic approach: forget with common option variations
            // This covers most use cases while being efficient
            $commonVariations = [
                [], // Default options
                ['includeInactive' => true],
                ['labelFormat' => 'name'],
                ['labelFormat' => 'code_name'],
                ['labelFormat' => 'name_code'],
            ];

            foreach ($commonVariations as $variation) {
                $options = array_merge([
                    'valueField' => 'id',
                    'labelFormat' => 'code_name',
                    'orderBy' => 'name',
                    'orderDirection' => 'asc',
                    'filters' => [],
                    'with' => [],
                    'includeInactive' => false,
                ], $variation);

                $key = self::generateCacheKey($modelClass, $options);
                Cache::forget($key);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONVENIENCE METHODS - MASTER DATA
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get departments dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getDepartments(array $options = []): array
    {
        return self::get(Department::class, array_merge([
            'labelFormat' => 'name', // Departments typically don't show code
        ], $options));
    }

    /**
     * Get UOMs (Units of Measure) dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getUoms(array $options = []): array
    {
        return self::get(Uom::class, $options);
    }

    /**
     * Get warehouses dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getWarehouses(array $options = []): array
    {
        return self::get(Warehouse::class, $options);
    }

    /**
     * Get taxes dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getTaxes(array $options = []): array
    {
        return self::get(Tax::class, $options);
    }

    /**
     * Get countries dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCountries(array $options = []): array
    {
        return self::get(Country::class, $options);
    }

    /**
     * Get currencies dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCurrencies(array $options = []): array
    {
        return self::get(Currency::class, $options);
    }

    /**
     * Get companies dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCompanies(array $options = []): array
    {
        return self::get(Company::class, $options);
    }

    /**
     * Get credit terms dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCreditTerms(array $options = []): array
    {
        return self::get(CreditTerm::class, $options);
    }

    /**
     * Get user groups dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getUserGroups(array $options = []): array
    {
        return self::get(UserGroup::class, array_merge([
            'labelFormat' => 'name', // User groups typically don't show code
        ], $options));
    }

    /**
     * Get employees dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getEmployees(array $options = []): array
    {
        return self::get(Employee::class, $options);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONVENIENCE METHODS - PARTNERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get suppliers dropdown data (partners with is_supplier = true).
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getSuppliers(array $options = []): array
    {
        return self::get(Partner::class, array_merge([
            'filters' => ['is_supplier' => true],
        ], $options));
    }

    /**
     * Get customers dropdown data (partners with is_customer = true).
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCustomers(array $options = []): array
    {
        return self::get(Partner::class, array_merge([
            'filters' => ['is_customer' => true],
        ], $options));
    }

    /**
     * Get customer addresses dropdown data.
     *
     * @param  int|null  $customerId  Filter by specific customer ID
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCustomerAddresses(?int $customerId = null, array $options = []): array
    {
        $filters = ['partner.is_customer' => true];

        if ($customerId !== null) {
            $filters['partner_id'] = $customerId;
        }

        return self::get(PartnerAddress::class, array_merge([
            'with' => ['partner'],
            'filters' => $filters,
            'labelFormat' => fn ($item) => "{$item->partner->name} - {$item->label}",
        ], $options));
    }

    /**
     * Get supplier addresses dropdown data.
     *
     * @param  int|null  $supplierId  Filter by specific supplier ID
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getSupplierAddresses(?int $supplierId = null, array $options = []): array
    {
        $filters = ['partner.is_supplier' => true];

        if ($supplierId !== null) {
            $filters['partner_id'] = $supplierId;
        }

        return self::get(PartnerAddress::class, array_merge([
            'with' => ['partner'],
            'filters' => $filters,
            'labelFormat' => fn ($item) => "{$item->partner->name} - {$item->label}",
        ], $options));
    }

    /**
     * Get all partner addresses dropdown data (not filtered by customer/supplier).
     *
     * @param  int|null  $partnerId  Filter by specific partner ID
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getPartnerAddresses(?int $partnerId = null, array $options = []): array
    {
        $filters = [];

        if ($partnerId !== null) {
            $filters['partner_id'] = $partnerId;
        }

        return self::get(PartnerAddress::class, array_merge([
            'with' => ['partner'],
            'filters' => $filters,
            'labelFormat' => fn ($item) => "{$item->partner->name} - {$item->label}",
        ], $options));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONVENIENCE METHODS - INVENTORY
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get items dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getItems(array $options = []): array
    {
        return self::get(Item::class, $options);
    }

    /**
     * Get category prices dropdown data.
     *
     * @param  array<string, mixed>  $options  Additional options
     * @return array<int, array{value: int, label: string}>
     */
    public static function getCategoryPrices(array $options = []): array
    {
        return self::get(CategoryPrice::class, $options);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPER METHODS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Format label based on format specification.
     *
     * @param  Model  $item  The model instance
     * @param  string|callable  $format  Format specification or closure
     * @return string Formatted label
     */
    private static function formatLabel(Model $item, string|callable $format): string
    {
        // If closure provided, use it directly
        if (is_callable($format)) {
            return (string) $format($item);
        }

        // Handle predefined formats
        return match ($format) {
            'name' => $item->name ?? '',
            'code' => $item->code ?? '',
            'name_code' => isset($item->name, $item->code) ? "{$item->name} ({$item->code})" : ($item->name ?? $item->code ?? ''),
            'code_name' => isset($item->code, $item->name) ? "{$item->code} - {$item->name}" : ($item->code ?? $item->name ?? ''),
            default => isset($item->code, $item->name) ? "{$item->code} - {$item->name}" : ($item->name ?? $item->code ?? ''),
        };
    }

    /**
     * Generate cache key from model class and options.
     *
     * @param  string  $modelClass  Model class name
     * @param  array<string, mixed>  $options  Options array
     * @return string Cache key
     */
    private static function generateCacheKey(string $modelClass, array $options): string
    {
        $modelName = class_basename($modelClass);

        // Create hash from relevant options (exclude non-query affecting options)
        $relevantOptions = [
            'filters' => $options['filters'],
            'includeInactive' => $options['includeInactive'],
            'orderBy' => $options['orderBy'],
            'orderDirection' => $options['orderDirection'],
            'with' => $options['with'],
            'labelFormat' => is_callable($options['labelFormat']) ? 'closure' : $options['labelFormat'],
            'valueField' => $options['valueField'],
        ];

        $hash = md5(json_encode($relevantOptions));

        return self::CACHE_PREFIX.'.'.$modelName.'.'.$hash;
    }

    /**
     * Generate default label text from model class name.
     *
     * @param  string  $modelClass  Model class name
     * @return string Default label (e.g., "Select Warehouse")
     */
    private static function generateDefaultLabel(string $modelClass): string
    {
        $modelName = class_basename($modelClass);

        // Convert PascalCase to words (e.g., "PartnerAddress" -> "Partner Address")
        $words = preg_replace('/([a-z])([A-Z])/', '$1 $2', $modelName);

        return "Select {$words}";
    }
}
