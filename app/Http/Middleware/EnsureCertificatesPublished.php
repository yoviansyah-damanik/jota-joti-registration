<?php

namespace App\Http\Middleware;

use App\Services\CertificateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * E-sertifikat untuk publik hanya dapat diunduh setelah kegiatan dinyatakan selesai.
 */
class EnsureCertificatesPublished
{
    public function __construct(private CertificateService $certificates) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->certificates->published()) {
            return redirect()->route('home')->with('toast', [
                'message' => __('Certificates are not available yet.'),
                'type' => 'warning',
            ]);
        }

        return $next($request);
    }
}
