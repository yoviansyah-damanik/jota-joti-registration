<?php

namespace App\Enums;

/**
 * Satuan (golongan) kepramukaan gudep yang mendaftar.
 */
enum ScoutUnit: string
{
    case Penggalang = 'penggalang';
    case PenegakPandega = 'penegak_pandega';

    public function label(): string
    {
        return match ($this) {
            self::Penggalang => 'Penggalang',
            self::PenegakPandega => 'Penegak/Pandega',
        };
    }

    /**
     * Keterangan rentang usia sebagai petunjuk bagi pendaftar (tidak divalidasi).
     */
    public function description(): string
    {
        return match ($this) {
            self::Penggalang => __('Ages 11–15'),
            self::PenegakPandega => __('Ages 16–25'),
        };
    }

    /**
     * @return array<string, string> nilai => label, untuk pilihan filter/form
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $unit) => [$unit->value => $unit->label()])->all();
    }
}
