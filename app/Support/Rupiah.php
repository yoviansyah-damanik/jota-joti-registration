<?php

namespace App\Support;

class Rupiah
{
    /**
     * Format rupiah Indonesia, mis. "Rp 250.000".
     */
    public static function format(int|float|null $amount): string
    {
        return 'Rp '.number_format($amount ?? 0, 0, ',', '.');
    }
}
