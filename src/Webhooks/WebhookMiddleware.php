<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebhookMiddleware
{
    public function __construct(
        private readonly WebhookSignatureVerifier $verifier,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $salt = $request->header('salt', '');
        $timestamp = $request->header('timestamp', '');
        $signature = $request->header('signature', '');

        if ($salt === '' || $timestamp === '' || $signature === '') {
            return response()->json(['error' => 'Missing webhook signature headers'], 403);
        }

        $urlPath = config('rapyd.webhook.path', '/rapyd/webhook');
        $tolerance = (int) config('rapyd.webhook.tolerance', 60);
        $body = $request->getContent();

        if (! $this->verifier->verify($urlPath, $salt, $timestamp, $signature, $body, $tolerance)) {
            return response()->json(['error' => 'Invalid webhook signature'], 403);
        }

        return $next($request);
    }
}
