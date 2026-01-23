<?php

declare(strict_types=1);

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'effective_date',
        'rate',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'rate' => 'decimal:6',
            'is_active' => 'boolean',
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get the source currency.
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * Get the target currency.
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATIC METHODS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Get the exchange rate between two currencies for a specific date.
     * Returns rate if found, 0 as fallback if not found.
     *
     * @param  int  $fromCurrencyId  Source currency ID
     * @param  int  $toCurrencyId  Target currency ID
     * @param  string|\Carbon\Carbon|null  $date  Date to get rate for (defaults to today)
     * @return float The exchange rate (0 if not found)
     */
    public static function getRate(int $fromCurrencyId, int $toCurrencyId, $date = null): float
    {
        // Same currency = rate of 1
        if ($fromCurrencyId === $toCurrencyId) {
            return 1.0;
        }

        $date = $date ? Carbon::parse($date) : now();

        $rate = self::query()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->where('effective_date', '<=', $date)
            ->where('is_active', true)
            ->orderByDesc('effective_date')
            ->first();

        return $rate ? (float) $rate->rate : 0.0;
    }

    /**
     * Get the exchange rate as display string (returns 'N/A' if not found).
     * Use this for DataTable displays.
     *
     * @param  int  $fromCurrencyId  Source currency ID
     * @param  int  $toCurrencyId  Target currency ID
     * @param  string|\Carbon\Carbon|null  $date  Date to get rate for (defaults to today)
     * @return string The exchange rate formatted or 'N/A'
     */
    public static function getRateDisplay(int $fromCurrencyId, int $toCurrencyId, $date = null): string
    {
        // Same currency = rate of 1
        if ($fromCurrencyId === $toCurrencyId) {
            return '1.000000';
        }

        $date = $date ? Carbon::parse($date) : now();

        $rate = self::query()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->where('effective_date', '<=', $date)
            ->where('is_active', true)
            ->orderByDesc('effective_date')
            ->first();

        return $rate ? number_format((float) $rate->rate, 6) : 'N/A';
    }

    /**
     * Convert amount from one currency to another using the appropriate rate.
     *
     * @param  float  $amount  Amount to convert
     * @param  int  $fromCurrencyId  Source currency ID
     * @param  int  $toCurrencyId  Target currency ID
     * @param  string|\Carbon\Carbon|null  $date  Date to get rate for (defaults to today)
     * @return float|null Converted amount (null if rate not found)
     */
    public static function convert(float $amount, int $fromCurrencyId, int $toCurrencyId, $date = null): ?float
    {
        $rate = self::getRate($fromCurrencyId, $toCurrencyId, $date);

        if ($rate === 0.0 && $fromCurrencyId !== $toCurrencyId) {
            return null;
        }

        return $amount * $rate;
    }

    /**
     * Create or update exchange rate and its reciprocal.
     *
     * @param  int  $fromCurrencyId  Source currency ID
     * @param  int  $toCurrencyId  Target currency ID
     * @param  string  $effectiveDate  Effective date
     * @param  float  $rate  Exchange rate
     * @param  string|null  $remarks  Optional remarks
     * @param  int|null  $userId  User performing the action
     * @return array{primary: ExchangeRate, reciprocal: ExchangeRate}
     */
    public static function createOrUpdateWithReciprocal(
        int $fromCurrencyId,
        int $toCurrencyId,
        string $effectiveDate,
        float $rate,
        ?string $remarks = null,
        ?int $userId = null
    ): array {
        // Create/update primary rate
        $primary = self::updateOrCreate(
            [
                'from_currency_id' => $fromCurrencyId,
                'to_currency_id' => $toCurrencyId,
                'effective_date' => $effectiveDate,
            ],
            [
                'rate' => $rate,
                'remarks' => $remarks,
                'is_active' => true,
                'updated_by' => $userId,
            ]
        );

        // If it was just created, set created_by
        if ($primary->wasRecentlyCreated) {
            $primary->update(['created_by' => $userId]);
        }

        // Calculate reciprocal rate (rounded to 6 decimals)
        $reciprocalRate = $rate > 0 ? round(1 / $rate, 6) : 0;

        // Create/update reciprocal rate
        $reciprocal = self::updateOrCreate(
            [
                'from_currency_id' => $toCurrencyId,
                'to_currency_id' => $fromCurrencyId,
                'effective_date' => $effectiveDate,
            ],
            [
                'rate' => $reciprocalRate,
                'remarks' => $remarks ? "[Reciprocal] {$remarks}" : '[Reciprocal]',
                'is_active' => true,
                'updated_by' => $userId,
            ]
        );

        // If it was just created, set created_by
        if ($reciprocal->wasRecentlyCreated) {
            $reciprocal->update(['created_by' => $userId]);
        }

        return ['primary' => $primary, 'reciprocal' => $reciprocal];
    }
}
