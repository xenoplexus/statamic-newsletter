<?php

namespace Xenoplexus\StatamicNewsletter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyPostmarkWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('statamic-newsletter.postmark.webhook_token');

        if (! $expectedToken) {
            Log::error('Newsletter Webhook: POSTMARK_WEBHOOK_TOKEN not configured.');

            return response()->json(['error' => 'Server misconfigured'], 500);
        }

        if ($request->header('X-Webhook-Token') !== $expectedToken) {
            Log::warning('Newsletter Webhook: Unauthorized request', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
