<?php

namespace App\Http\Middleware;

use App\Services\GroupAssignmentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Halaman kartu peserta untuk publik hanya dapat diakses setelah kartu diterbitkan (pembagian grup selesai).
 */
class EnsureCardsPublished
{
    public function __construct(private GroupAssignmentService $groups) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->groups->isCompleted()) {
            return redirect()->route('home')->with('toast', [
                'message' => __('Participant cards have not been published yet.'),
                'type' => 'warning',
            ]);
        }

        return $next($request);
    }
}
