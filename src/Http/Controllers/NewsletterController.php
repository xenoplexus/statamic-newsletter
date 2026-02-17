<?php

namespace Xenoplexus\StatamicNewsletter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use Xenoplexus\StatamicNewsletter\Services\NewsletterRenderer;

class NewsletterController extends Controller
{
    protected function subscriberTable()
    {
        return DB::connection(config('statamic-newsletter.database.connection'))
            ->table(config('statamic-newsletter.database.table', 'newsletter_subscribers'));
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        try {
            $existing = $this->subscriberTable()
                ->where('email', $email)
                ->first();

            if ($existing) {
                if ($existing->status === 'active') {
                    return response()->json([
                        'success' => true,
                        'message' => "You're already subscribed!",
                    ]);
                }

                $this->subscriberTable()
                    ->where('email', $email)
                    ->update([
                        'status' => 'active',
                        'subscribed' => now(),
                    ]);

                Log::info("Newsletter: Reactivated subscriber {$email}");
            } else {
                $this->subscriberTable()
                    ->insert([
                        'email' => $email,
                        'subscribed' => now(),
                        'status' => 'active',
                    ]);

                Log::info("Newsletter: New subscriber {$email}");
            }

            $this->sendWelcomeEmail($email);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing!',
            ]);

        } catch (\Exception $ex) {
            Log::error("Newsletter: Subscribe failed for {$email} — ".$ex->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function unsubscribe(Request $request, string $email): string
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or has expired.');
        }

        $email = strtolower(trim($email));

        try {
            $updated = $this->subscriberTable()
                ->where('email', $email)
                ->update(['status' => 'inactive']);

            if ($updated) {
                Log::info("Newsletter: Unsubscribed {$email}");
            }
        } catch (\Exception $ex) {
            Log::error("Newsletter: Unsubscribe failed for {$email} — ".$ex->getMessage());
        }

        $orgName = e(config('statamic-newsletter.organization.name', 'our organization'));
        $siteUrl = config('statamic-newsletter.organization.url', config('app.url'));

        return '<html><head><meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Unsubscribed</title></head>
            <body style="font-family:Arial,sans-serif;text-align:center;padding:60px 20px;background:#eef1f5">
            <div style="max-width:500px;margin:0 auto;background:#fff;border-radius:8px;padding:40px">
            <h2 style="color:#232f4b;margin-bottom:12px">You\'ve been unsubscribed</h2>
            <p style="color:#555;line-height:1.6">You will no longer receive newsletters from '.$orgName.'.</p>
            <p style="padding-top:20px"><a href="'.e($siteUrl).'" style="color:#f67d4a">Return to our website</a></p>
            </div></body></html>';
    }

    public function unsubscribePage()
    {
        $prefix = config('statamic-newsletter.routes.prefix', 'newsletter');

        return response()->file(public_path("{$prefix}/unsubscribe.html"));
    }

    public function preview(string $slug)
    {
        $collection = config('statamic-newsletter.statamic.collection', 'newsletters');

        $resolved = \Statamic\Facades\Entry::query()
            ->where('collection', $collection)
            ->where('slug', $slug)
            ->first();

        if (! $resolved) {
            abort(404);
        }

        return NewsletterRenderer::render($resolved);
    }

    public function unsubscribeByEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        try {
            $existing = $this->subscriberTable()
                ->where('email', $email)
                ->first();

            if (! $existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'If that email is on our list, it has been removed.',
                ]);
            }

            if ($existing->status === 'inactive') {
                return response()->json([
                    'success' => true,
                    'message' => 'This email is already unsubscribed.',
                ]);
            }

            $this->subscriberTable()
                ->where('email', $email)
                ->update(['status' => 'inactive']);

            Log::info("Newsletter: Unsubscribed {$email} (via form)");

            return response()->json([
                'success' => true,
                'message' => "You've been unsubscribed. You will no longer receive our newsletters.",
            ]);

        } catch (\Exception $ex) {
            Log::error("Newsletter: Form unsubscribe failed for {$email} — ".$ex->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    protected function sendWelcomeEmail(string $email): void
    {
        $token = config('statamic-newsletter.postmark.token');

        if (! $token) {
            Log::error('Newsletter: POSTMARK_TOKEN is not configured.');

            return;
        }

        $unsubscribeUrl = static::unsubscribeUrl($email);
        $html = view('statamic-newsletter::emails.welcome', array_merge(
            NewsletterRenderer::templateData(),
            ['unsubscribe_url' => $unsubscribeUrl]
        ))->render();

        $from = config('statamic-newsletter.email.from_address');
        $subject = config('statamic-newsletter.email.welcome_subject', 'Welcome to our newsletter!');
        $replyTo = config('statamic-newsletter.email.reply_to');
        $tag = config('statamic-newsletter.postmark.welcome_tag', 'welcome');
        $trackOpens = config('statamic-newsletter.postmark.track_opens', true);
        $trackLinks = config('statamic-newsletter.postmark.track_links', 'None');
        $stream = config('statamic-newsletter.postmark.welcome_stream', 'outbound');

        try {
            $client = new PostmarkClient($token);

            $client->sendEmail(
                $from,
                $email,
                $subject,
                $html,
                null,           // TextBody
                $tag,
                $trackOpens,
                $replyTo,
                null,           // Cc
                null,           // Bcc
                null,           // Headers
                null,           // Attachments
                $trackLinks,
                null,           // Metadata
                $stream
            );

            Log::info("Newsletter: Welcome email sent to {$email}");

        } catch (PostmarkException $ex) {
            Log::error("Newsletter: Postmark API error sending welcome to {$email} — HTTP {$ex->httpStatusCode}: {$ex->message}");
        } catch (\Exception $ex) {
            Log::error("Newsletter: Error sending welcome to {$email} — ".$ex->getMessage());
        }
    }

    public static function unsubscribeUrl(string $email): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['email' => $email]);
    }

    public function handleSuppressionWebhook(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        $suppressSending = $payload['SuppressSending'] ?? null;
        $reason = $payload['SuppressionReason'] ?? null;
        $email = $payload['Recipient'] ?? null;
        $origin = $payload['Origin'] ?? null;
        $changedAt = $payload['ChangedAt'] ?? null;

        if ($suppressSending !== true) {
            Log::info('Newsletter Webhook: Ignoring non-suppression event', [
                'email' => $email,
                'suppress_sending' => $suppressSending,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $validReasons = ['HardBounce', 'SpamComplaint', 'ManualSuppression'];
        if (! in_array($reason, $validReasons, true)) {
            Log::warning('Newsletter Webhook: Unknown suppression reason', [
                'email' => $email,
                'reason' => $reason,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Newsletter Webhook: Invalid or missing recipient email', [
                'recipient' => $email,
            ]);

            return response()->json(['error' => 'Invalid recipient'], 422);
        }

        $email = strtolower(trim($email));

        try {
            $updated = $this->subscriberTable()
                ->where('email', $email)
                ->update(['status' => 'inactive']);

            if ($updated) {
                Log::info("Newsletter Webhook: Suppressed {$email}", [
                    'reason' => $reason,
                    'origin' => $origin,
                    'changed_at' => $changedAt,
                ]);
            } else {
                Log::info("Newsletter Webhook: Suppression for unknown email {$email}", [
                    'reason' => $reason,
                ]);
            }
        } catch (\Exception $ex) {
            Log::error("Newsletter Webhook: Database error suppressing {$email} — ".$ex->getMessage());

            return response()->json(['error' => 'Internal error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
