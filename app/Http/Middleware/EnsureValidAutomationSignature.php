<?php

namespace App\Http\Middleware;

use App\Support\AutomationSignature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidAutomationSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.n8n.shared_secret', '');
        $signature = (string) $request->header('X-Radiochi-Signature', '');
        $timestamp = (string) $request->header('X-Radiochi-Timestamp', '');
        $clockSkew = (int) config('services.n8n.allowed_clock_skew', 300);

        abort_if($secret === '' || $signature === '' || $timestamp === '', 401, 'Automation signature is missing.');
        abort_if(! ctype_digit($timestamp), 401, 'Automation timestamp is invalid.');
        abort_if(abs(now()->timestamp - (int) $timestamp) > $clockSkew, 401, 'Automation signature has expired.');

        $body = $request->getContent();

        abort_unless(
            AutomationSignature::verify($secret, $timestamp, $body, $signature),
            401,
            'Automation signature is invalid.',
        );

        return $next($request);
    }
}
