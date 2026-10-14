<?php

namespace App\Services;

use App\Enums\ScoutUnit;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    private const CACHE_KEY = 'app_settings';

    /**
     * Nilai bawaan dipakai bila key belum tersimpan di database.
     */
    public const DEFAULTS = [
        'event_name' => 'JOTA JOTI 2026',
        'organization_name' => 'Kwarcab Kota Padangsidimpuan',
        'registration_open' => '1',
        'max_group_number' => '10',
        'penggalang_group_count' => '5',
        'penegak_group_count' => '5',
        'penggalang_start_number' => '1',
        'penegak_start_number' => '',
        'min_per_number' => '1',
        'registration_fee' => '0',
        'bank_account_info' => '',
        'contact_info' => '',
        'additional_terms' => '',
        'event_completed' => '0',
        'event_completed_at' => '',
        'certificate_signer_name' => '',
        'certificate_signer_title' => 'Ketua Kwartir Cabang Kota Padangsidimpuan',
        'certificate_city' => 'Padangsidimpuan',
        'official_logo_path' => '',
    ];

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::pluck('value', 'key')->all());

        return array_merge(self::DEFAULTS, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function int(string $key): int
    {
        return (int) $this->get($key, 0);
    }

    public function bool(string $key): bool
    {
        return filter_var($this->get($key, false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? (int) $value : $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function penggalangGroupCount(): int
    {
        return max(1, $this->int('penggalang_group_count', 5));
    }

    public function penegakGroupCount(): int
    {
        return max(1, $this->int('penegak_group_count', 5));
    }

    public function penggalangStartNumber(): int
    {
        return max(1, $this->int('penggalang_start_number', 1));
    }

    public function penegakStartNumber(): int
    {
        $custom = $this->int('penegak_start_number');
        if ($custom > 0) {
            return $custom;
        }

        return $this->penggalangStartNumber() + $this->penggalangGroupCount();
    }

    /**
     * @return array<int> daftar nomor grup untuk Penggalang
     */
    public function penggalangGroups(): array
    {
        $start = $this->penggalangStartNumber();
        $count = $this->penggalangGroupCount();

        return range($start, $start + $count - 1);
    }

    /**
     * @return array<int> daftar nomor grup untuk Penegak/Pandega
     */
    public function penegakGroups(): array
    {
        $start = $this->penegakStartNumber();
        $count = $this->penegakGroupCount();

        return range($start, $start + $count - 1);
    }

    /**
     * @return array<int> daftar nomor grup yang dialokasikan untuk satuan tertentu
     */
    public function groupsForUnit(ScoutUnit|string|null $unit): array
    {
        $scoutUnit = is_string($unit) ? ScoutUnit::tryFrom($unit) : $unit;

        return $scoutUnit === ScoutUnit::PenegakPandega
            ? $this->penegakGroups()
            : $this->penggalangGroups();
    }

    /**
     * Golongan yang berhak menempati nomor grup tertentu.
     */
    public function unitForGroup(int $groupNumber): ?ScoutUnit
    {
        if (in_array($groupNumber, $this->penggalangGroups(), true)) {
            return ScoutUnit::Penggalang;
        }

        if (in_array($groupNumber, $this->penegakGroups(), true)) {
            return ScoutUnit::PenegakPandega;
        }

        return null;
    }

    /**
     * Seluruh nomor grup yang dialokasikan (terurut).
     *
     * @return array<int>
     */
    public function allGroupNumbers(): array
    {
        $all = array_merge($this->penggalangGroups(), $this->penegakGroups());
        sort($all);

        return array_values(array_unique($all));
    }

    /**
     * Nomor grup tertinggi di antara seluruh golongan.
     */
    public function maxGroupNumber(): int
    {
        $all = $this->allGroupNumbers();

        return $all !== [] ? max($all) : max(1, $this->int('max_group_number', 10));
    }

    public function minPerNumber(): int
    {
        return max(0, $this->int('min_per_number'));
    }

    /**
     * Jumlah minimal peserta per gudep agar setiap nomor grup terisi sesuai konfigurasi.
     */
    public function minParticipantsPerTroop(?ScoutUnit $unit = null): int
    {
        $minPerNumber = $this->minPerNumber();
        if ($unit === ScoutUnit::Penggalang) {
            return $this->penggalangGroupCount() * $minPerNumber;
        }

        if ($unit === ScoutUnit::PenegakPandega) {
            return $this->penegakGroupCount() * $minPerNumber;
        }

        return min($this->penggalangGroupCount(), $this->penegakGroupCount()) * $minPerNumber;
    }

    public function registrationOpen(): bool
    {
        return $this->bool('registration_open');
    }

    public function eventCompleted(): bool
    {
        return $this->bool('event_completed');
    }

    /**
     * URL logo resmi (kustom jika diunggah, atau bawaan aplikasi).
     */
    public function logoUrl(): string
    {
        $path = $this->get('official_logo_path');
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/logo.webp');
    }

    /**
     * Path absolut berkas logo resmi di filesystem.
     */
    public function logoPath(): string
    {
        $path = $this->get('official_logo_path');
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return public_path('images/logo.webp');
    }

    /**
     * Menandakan apakah logo yang digunakan merupakan hasil unggahan kustom.
     */
    public function hasCustomLogo(): bool
    {
        $path = $this->get('official_logo_path');

        return (bool) ($path && Storage::disk('public')->exists($path));
    }
}
