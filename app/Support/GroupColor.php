<?php

namespace App\Support;

/**
 * Warna tetap untuk setiap nomor grup agar mudah dibedakan di layar maupun di kartu cetak.
 * 12 grup pertama memakai palet yang mudah dibedakan; selebihnya dihasilkan dari sudut hue emas.
 */
class GroupColor
{
    /** @var array<int, string> warna heksadesimal per nomor grup */
    private const PALETTE = [
        1 => '#dc2626',
        2 => '#2563eb',
        3 => '#16a34a',
        4 => '#f59e0b',
        5 => '#7c3aed',
        6 => '#ea580c',
        7 => '#0891b2',
        8 => '#db2777',
        9 => '#65a30d',
        10 => '#4338ca',
        11 => '#92400e',
        12 => '#475569',
    ];

    private const NEUTRAL = '#9ca3af';

    /**
     * @return array{background: string, text: string}
     */
    public static function for(?int $number): array
    {
        if (! $number || $number < 1) {
            return ['background' => self::NEUTRAL, 'text' => '#111827'];
        }

        $hex = self::PALETTE[$number] ?? self::generated($number);

        return [
            'background' => $hex,
            'text' => self::readableText($hex),
        ];
    }

    /**
     * Versi sangat muda dari warna grup (dicampur putih) untuk cincin/latar lembut.
     */
    public static function tint(?int $number, float $amount = 0.86): string
    {
        [$r, $g, $b] = sscanf(self::for($number)['background'], '#%02x%02x%02x');
        $mix = fn (int $c) => (int) round($c + (255 - $c) * $amount);

        return sprintf('#%02x%02x%02x', $mix($r), $mix($g), $mix($b));
    }
    /**
     * Warna untuk grup di atas 12: hue diputar 137,5° tiap nomor sehingga tetangga tidak mirip.
     */
    private static function generated(int $number): string
    {
        $hue = fmod(($number - 13) * 137.508 + 20, 360);
        [$r, $g, $b] = self::hslToRgb($hue, 0.62, 0.42);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Hitam atau putih, mana yang kontrasnya lebih baik terhadap warna latar.
     */
    private static function readableText(string $hex): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
        $luminance = collect([$r, $g, $b])->map(function (int $channel) {
            $c = $channel / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        })->pipe(fn ($v) => 0.2126 * $v[0] + 0.7152 * $v[1] + 0.0722 * $v[2]);

        return $luminance > 0.4 ? '#111827' : '#ffffff';
    }

    /**
     * @return array{int, int, int}
     */
    private static function hslToRgb(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [(int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255)];
    }
}
