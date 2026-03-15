<?php

declare(strict_types=1);

namespace App\Helpers\CMW;

class NumberToWordsHelper
{
    private static array $words = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
        'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas',
    ];

    /**
     * Convert a number to Indonesian words (terbilang).
     *
     * @param  float|int  $amount  The amount to convert
     * @param  string  $currency  Currency suffix (default: 'Rupiah')
     * @return string The amount in words
     */
    public static function convert(float|int $amount, string $currency = 'Rupiah'): string
    {
        $amount = abs($amount);
        $integerPart = (int) floor($amount);

        if ($integerPart === 0) {
            return 'Nol '.$currency;
        }

        return trim(self::say($integerPart)).' '.$currency;
    }

    private static function say(int $number): string
    {
        if ($number < 12) {
            return self::$words[$number];
        }

        if ($number < 20) {
            return self::$words[$number - 10].' Belas';
        }

        if ($number < 100) {
            return self::$words[(int) ($number / 10)].' Puluh '.self::say($number % 10);
        }

        if ($number < 200) {
            return 'Seratus '.self::say($number - 100);
        }

        if ($number < 1000) {
            return self::$words[(int) ($number / 100)].' Ratus '.self::say($number % 100);
        }

        if ($number < 2000) {
            return 'Seribu '.self::say($number - 1000);
        }

        if ($number < 1_000_000) {
            return self::say((int) ($number / 1000)).' Ribu '.self::say($number % 1000);
        }

        if ($number < 1_000_000_000) {
            return self::say((int) ($number / 1_000_000)).' Juta '.self::say($number % 1_000_000);
        }

        if ($number < 1_000_000_000_000) {
            return self::say((int) ($number / 1_000_000_000)).' Miliar '.self::say($number % 1_000_000_000);
        }

        return self::say((int) ($number / 1_000_000_000_000)).' Triliun '.self::say($number % 1_000_000_000_000);
    }
}
