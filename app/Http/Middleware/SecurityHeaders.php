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

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }

    private function contentSecurityPolicy(Request $request): string
    {
        $directives = [
            'default-src' => ["'self'"],
            'img-src' => ["'self'", 'https:', 'data:'],
            'media-src' => ["'self'", 'https:'],
            'font-src' => ["'self'", 'https:', 'data:'],
            'style-src' => ["'self'", "'unsafe-inline'", 'https:'],
            'script-src' => ["'self'", "'unsafe-inline'", 'https:'],
            'connect-src' => ["'self'", 'https:'],
            'frame-src' => ['https://www.youtube.com', 'https://www.youtube-nocookie.com', 'https://w.soundcloud.com'],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
        ];

        if ($this->shouldAllowLocalViteHmr()) {
            $directives['style-src'] = array_merge($directives['style-src'], $this->localViteHttpOrigins());
            $directives['script-src'] = array_merge($directives['script-src'], $this->localViteHttpOrigins());
            $directives['connect-src'] = array_merge(
                $directives['connect-src'],
                $this->localViteHttpOrigins(),
                $this->localViteWsOrigins(),
            );
        }

        return implode('; ', array_map(
            static fn (string $directive, array $sources): string => $directive . ' ' . implode(' ', array_values(array_unique($sources))),
            array_keys($directives),
            $directives,
        ));
    }

    private function shouldAllowLocalViteHmr(): bool
    {
        // Keep Vite dev-server origins scoped to local development only.
        return app()->environment('local') && is_file(public_path('hot'));
    }

    /**
     * @return array<int, string>
     */
    private function localViteHttpOrigins(): array
    {
        return [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function localViteWsOrigins(): array
    {
        return [
            'ws://localhost:5173',
            'ws://127.0.0.1:5173',
        ];
    }
}
