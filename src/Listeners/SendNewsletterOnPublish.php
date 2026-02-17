<?php

namespace Xenoplexus\StatamicNewsletter\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Statamic\Events\EntrySaved;
use Xenoplexus\StatamicNewsletter\Jobs\SendNewsletterBatch;
use Xenoplexus\StatamicNewsletter\Services\NewsletterRenderer;

class SendNewsletterOnPublish
{
    public function handle(EntrySaved $event): void
    {
        $entry = $event->entry;
        $collection = config('statamic-newsletter.statamic.collection', 'newsletters');

        if ($entry->collection()?->handle() !== $collection) {
            return;
        }

        if (! $entry->published()) {
            return;
        }

        if ($entry->getOriginal('published') === true) {
            return;
        }

        if ($entry->get('newsletter_sent_at')) {
            Log::info("Newsletter: Skipping '{$entry->get('title')}' — already sent at {$entry->get('newsletter_sent_at')}.");

            return;
        }

        Log::info("Newsletter: Publishing '{$entry->get('title')}' — preparing to send.");

        try {
            $subscribers = DB::connection(config('statamic-newsletter.database.connection'))
                ->table(config('statamic-newsletter.database.table', 'newsletter_subscribers'))
                ->where('status', 'active')
                ->pluck('email')
                ->toArray();
        } catch (\Exception $ex) {
            Log::error('Newsletter: Failed to fetch subscribers — '.$ex->getMessage());

            return;
        }

        if (empty($subscribers)) {
            Log::warning('Newsletter: No active subscribers found. Aborting send.');

            return;
        }

        Log::info('Newsletter: Found '.count($subscribers).' active subscribers.');

        $html = NewsletterRenderer::render($entry);
        $subject = $entry->get('title');

        $batchSize = (int) config('statamic-newsletter.sending.batch_size', 50);
        $chunks = array_chunk($subscribers, $batchSize);

        foreach ($chunks as $index => $batch) {
            SendNewsletterBatch::dispatch($html, $subject, $batch);
            Log::info('Newsletter: Dispatched batch '.($index + 1).' of '.count($chunks).' ('.count($batch).' recipients).');
        }

        $entry->set('newsletter_sent_at', now()->toIso8601String());
        $entry->set('newsletter_send_count', count($subscribers));
        $entry->saveQuietly();

        Log::info("Newsletter: '{$subject}' dispatched to ".count($subscribers).' subscribers in '.count($chunks).' batches.');
    }
}
