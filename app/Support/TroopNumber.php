<?php

namespace App\Support;

/**
 * Nomor gudep berpasangan format xx-xxx & xx-xxx, nomor kedua = nomor pertama + 1.
 * Contoh: 01-083 → 01-083 & 01-084.
 */
class TroopNumber
{
    public const PATTERN = '/^\d{2}-\d{3}$/';

    /**
     * Merapikan input pengguna (mis. "01083", "01.083", "01 083") menjadi "01-083".
     */
    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);
        $digits = preg_replace('/\D/', '', $value);

        return strlen($digits) === 5 ? substr($digits, 0, 2).'-'.substr($digits, 2) : $value;
    }

    /**
     * Nomor pertama valid bila sesuai format, tiga digit terakhirnya ganjil, dan masih bisa ditambah 1.
     */
    public static function isValid(?string $first): bool
    {
        return (bool) preg_match(self::PATTERN, (string) $first)
            && self::isOdd($first)
            && (int) substr($first, 3) < 999;
    }

    public static function isOdd(string $first): bool
    {
        return (int) substr($first, 3) % 2 === 1;
    }

    public static function pair(?string $first): ?string
    {
        if (! self::isValid($first)) {
            return null;
        }

        return substr($first, 0, 3).str_pad((string) ((int) substr($first, 3) + 1), 3, '0', STR_PAD_LEFT);
    }

    public static function label(?string $first, ?string $pair = null): string
    {
        $pair ??= self::pair($first);

        return $pair ? "{$first} & {$pair}" : (string) $first;
    }
}
