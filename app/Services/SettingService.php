<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

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
        'max_group_number' => '5',
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

    public function maxGroupNumber(): int
    {
        return max(1, $this->int('max_group_number'));
    }

    public function minPerNumber(): int
    {
        return max(0, $this->int('min_per_number'));
    }

    /**
     * Jumlah minimal peserta per gudep agar setiap nomor grup terisi sesuai konfigurasi.
     */
    public function minParticipantsPerTroop(): int
    {
        return $this->maxGroupNumber() * $this->minPerNumber();
    }

    public function registrationOpen(): bool
    {
        return $this->bool('registration_open');
    }

    public function eventCompleted(): bool
    {
        return $this->bool('event_completed');
    }
}
