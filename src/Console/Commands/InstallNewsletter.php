<?php

namespace Xenoplexus\StatamicNewsletter\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallNewsletter extends Command
{
    protected $signature = 'newsletter:install {--force : Overwrite existing files}';

    protected $description = 'Install the newsletter addon (collection, blueprint, unsubscribe page)';

    public function handle(): int
    {
        $force = $this->option('force');
        $addonPath = realpath(__DIR__.'/../../../');

        // 1. Collection config
        $this->copyFile(
            "{$addonPath}/resources/content/newsletters.yaml",
            content_path('collections/newsletters.yaml'),
            $force
        );

        // 2. Blueprint
        $blueprintDir = resource_path('blueprints/collections/newsletters');
        File::ensureDirectoryExists($blueprintDir);
        $this->copyFile(
            "{$addonPath}/resources/blueprints/newsletter.yaml",
            "{$blueprintDir}/newsletter.yaml",
            $force
        );

        // 3. Unsubscribe page
        $prefix = config('statamic-newsletter.routes.prefix', 'newsletter');
        $publicDir = public_path($prefix);
        File::ensureDirectoryExists($publicDir);
        $this->copyFile(
            "{$addonPath}/stubs/unsubscribe.html",
            "{$publicDir}/unsubscribe.html",
            $force
        );

        // 4. Run migration
        $this->call('migrate');

        // 5. Print env guidance
        $this->newLine();
        $this->info('Newsletter addon installed!');
        $this->newLine();
        $this->comment('Add these to your .env file:');
        $this->line('  POSTMARK_TOKEN=your-postmark-server-token');
        $this->line('  POSTMARK_WEBHOOK_TOKEN=your-webhook-secret');
        $this->line('  NEWSLETTER_ORG_NAME="Your Organization"');
        $this->line('  NEWSLETTER_ORG_EMAIL=info@example.com');
        $this->line('  NEWSLETTER_ORG_ADDRESS="Your Address"');
        $this->line('  NEWSLETTER_FROM_ADDRESS=info@example.com');
        $this->line('  NEWSLETTER_NOREPLY_ADDRESS=no.reply@example.com');
        $this->newLine();
        $this->comment('To customize email templates:');
        $this->line('  php artisan vendor:publish --tag=statamic-newsletter-views');

        return 0;
    }

    protected function copyFile(string $from, string $to, bool $force): void
    {
        if (File::exists($to) && ! $force) {
            $this->warn("  Skipped: {$to} (already exists, use --force to overwrite)");

            return;
        }

        File::copy($from, $to);
        $this->info("  Copied: {$to}");
    }
}
