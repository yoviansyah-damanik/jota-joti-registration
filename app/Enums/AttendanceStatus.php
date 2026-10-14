<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => __('Present'),
            self::Absent => __('Absent'),
            self::Excused => __('Excused'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'badge-success',
            self::Absent => 'badge-error',
            self::Excused => 'badge-warning',
        };
    }
}
