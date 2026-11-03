<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        // CSP untuk halaman HTML. Dilewati untuk unduhan CV agar penampil PDF browser tetap bekerja.
        // 'unsafe-inline' dibutuhkan karena view memakai script/style inline dan Tailwind CDN.
        // 'unsafe-eval' dan cdn.tailwindcss.com hanya untuk Tailwind CDN. Setelah Tailwind di-build lewat Vite, hapus keduanya (lihat docs/SECURITY.md).
        if (! $request->routeIs('candidates.cv', 'api.v1.candidates.cv')) {
            $headers['Content-Security-Policy'] = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data:",
                "font-src 'self' data:",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
            ]);
        }

        // Halaman yang butuh login dan semua API: jangan disimpan cache (tidak bisa dibuka lewat tombol Back setelah logout).
        if ($request->user() || $request->is('api/*')) {
            $headers['Cache-Control'] = 'no-store, private';
            $headers['X-Robots-Tag'] = 'noindex, nofollow';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
