<?php

namespace Xenoplexus\StatamicNewsletter\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Entry;
use Xenoplexus\StatamicNewsletter\Services\NewsletterRenderer;

class PreviewNewsletter extends Command
{
    protected $signature = 'newsletter:preview {slug?} {--demo : Preview with placeholder lorem ipsum content}';

    protected $description = 'Render a newsletter preview and open it in the browser';

    public function handle(): int
    {
        if ($this->option('demo')) {
            $html = NewsletterRenderer::renderDemo();

            return $this->saveAndOpen($html, 'demo');
        }

        $slug = $this->argument('slug');
        $collection = config('statamic-newsletter.statamic.collection', 'newsletters');

        if (! $slug) {
            $entries = Entry::query()
                ->where('collection', $collection)
                ->get()
                ->map(fn ($e) => $e->slug().' — '.$e->get('title'))
                ->toArray();

            if (empty($entries)) {
                $this->error('No newsletter entries found. Use --demo to preview with placeholder content.');

                return 1;
            }

            $choice = $this->choice('Which newsletter?', $entries);
            $slug = explode(' — ', $choice)[0];
        }

        $entry = Entry::query()
            ->where('collection', $collection)
            ->where('slug', $slug)
            ->first();

        if (! $entry) {
            $this->error("Newsletter with slug '{$slug}' not found.");

            return 1;
        }

        $html = NewsletterRenderer::render($entry);

        return $this->saveAndOpen($html, $slug);
    }

    private function saveAndOpen(string $html, string $label): int
    {
        $path = storage_path('app/newsletter-preview.html');
        file_put_contents($path, $html);

        $this->info("Preview ({$label}) saved to: {$path}");
        $this->info('Opening in browser...');

        if (PHP_OS_FAMILY === 'Darwin') {
            exec('open '.escapeshellarg($path));
        } elseif (PHP_OS_FAMILY === 'Linux') {
            exec('xdg-open '.escapeshellarg($path));
        } elseif (PHP_OS_FAMILY === 'Windows') {
            exec('start '.escapeshellarg($path));
        }

        return 0;
    }
}
