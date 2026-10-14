<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'L';
    case Female = 'P';

    public function label(): string
    {
        return match ($this) {
            self::Male => __('Male'),
            self::Female => __('Female'),
        };
    }
}
