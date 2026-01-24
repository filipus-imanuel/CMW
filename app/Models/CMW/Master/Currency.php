<?php

namespace App\Models\CMW\Master;

use App\Models\CMW\BaseModel;

class Currency extends BaseModel
{
    protected $fillable = [
        'symbol',
        'symbol_position',
        'rate',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'rate' => 'decimal:2',
        ]);
    }

    /**
     * Format an amount with this currency's symbol.
     *
     * @param  float|int|string  $amount  The amount to format
     * @param  int  $decimals  Number of decimal places (default: 2)
     */
    public function formatAmount(float|int|string $amount, int $decimals = 2): string
    {
        $formattedAmount = number_format((float) $amount, $decimals);

        if ($this->symbol_position === 'AFTER') {
            return $formattedAmount.' '.$this->symbol;
        }

        return $this->symbol.' '.$formattedAmount;
    }
}
