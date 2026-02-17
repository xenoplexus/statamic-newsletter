<?php

namespace Xenoplexus\StatamicNewsletter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;

class SendNewsletterBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $backoff;

    public function __construct(
        public string $html,
        public string $subject,
        public array $emails,
    ) {
        $this->tries = (int) config('statamic-newsletter.sending.max_retries', 3);
        $this->backoff = (int) config('statamic-newsletter.sending.retry_backoff', 60);
    }

    public function handle(): void
    {
        $token = config('statamic-newsletter.postmark.token');

        if (! $token) {
            Log::error('Newsletter: POSTMARK_TOKEN is not configured.');
            $this->fail(new \RuntimeException('POSTMARK_TOKEN is not configured.'));

            return;
        }

        $bcc = implode(',', $this->emails);
        $from = config('statamic-newsletter.email.from_address');
        $to = config('statamic-newsletter.email.noreply_address');
        $replyTo = config('statamic-newsletter.email.noreply_address');
        $tag = config('statamic-newsletter.postmark.newsletter_tag', 'newsletter');
        $trackOpens = config('statamic-newsletter.postmark.track_opens', true);
        $trackLinks = config('statamic-newsletter.postmark.track_links', 'None');
        $stream = config('statamic-newsletter.postmark.broadcast_stream', 'broadcast');

        try {
            $client = new PostmarkClient($token);

            $client->sendEmail(
                $from,
                $to,
                $this->subject,
                $this->html,
                null,           // TextBody
                $tag,
                $trackOpens,
                $replyTo,
                null,           // Cc
                $bcc,
                null,           // Headers
                null,           // Attachments
                $trackLinks,
                null,           // Metadata
                $stream
            );

            Log::info('Newsletter: Batch sent to '.count($this->emails).' recipients via Postmark broadcast stream.');

        } catch (PostmarkException $ex) {
            Log::error("Newsletter: Postmark API error — HTTP {$ex->httpStatusCode}, code {$ex->postmarkApiErrorCode}: {$ex->message}");
            throw $ex;
        } catch (\Exception $ex) {
            Log::error('Newsletter: General error — '.$ex->getMessage());
            throw $ex;
        }
    }
}
