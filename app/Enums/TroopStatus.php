<?php

namespace App\Enums;

enum TroopStatus: string
{
    case Submitted = 'submitted';
    case Revision = 'revision';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => __('Awaiting Review'),
            self::Revision => __('Needs Revision'),
            self::Verified => __('Verified'),
            self::Rejected => __('Rejected'),
        };
    }

    /**
     * Kelas badge daisyUI untuk tiap status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'badge-info',
            self::Revision => 'badge-warning',
            self::Verified => 'badge-success',
            self::Rejected => 'badge-error',
        };
    }

    /**
     * Status yang masih "memakai" nomor gudep sehingga gudep yang sama tidak boleh mendaftar ulang.
     *
     * @return array<int, string>
     */
    public static function activeValues(): array
    {
        return [self::Submitted->value, self::Revision->value, self::Verified->value];
    }
}
