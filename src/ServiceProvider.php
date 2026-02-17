<?php

namespace Xenoplexus\StatamicNewsletter;

use Statamic\Events\EntrySaved;
use Statamic\Providers\AddonServiceProvider;
use Xenoplexus\StatamicNewsletter\Listeners\SendNewsletterOnPublish;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        EntrySaved::class => [
            SendNewsletterOnPublish::class,
        ],
    ];

    public function register(): void
    {
        parent::register();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function bootAddon(): void
    {
        $this->publishes([
            __DIR__.'/../stubs/unsubscribe.html' => public_path(
                config('statamic-newsletter.routes.prefix', 'newsletter').'/unsubscribe.html'
            ),
        ], 'statamic-newsletter-assets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/statamic-newsletter'),
        ], 'statamic-newsletter-views');
    }
}
