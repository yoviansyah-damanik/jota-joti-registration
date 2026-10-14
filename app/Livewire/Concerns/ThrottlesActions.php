<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait ThrottlesActions
{
    /**
     * Membatasi aksi publik per IP untuk mencegah spam / brute force.
     */
    protected function throttle(string $action, string $errorField, int $maxAttempts = 10, int $decaySeconds = 60): void
    {
        $key = $action.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                $errorField => __('Too many attempts. Please try again in :seconds seconds.', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
