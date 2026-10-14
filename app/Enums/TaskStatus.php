<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Revision = 'revision';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Menunggu Penilaian'),
            self::Completed => __('Selesai'),
            self::Revision => __('Revisi'),
            self::Rejected => __('Ditolak'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-info',
            self::Completed => 'badge-success',
            self::Revision => 'badge-warning',
            self::Rejected => 'badge-error',
        };
    }
}
