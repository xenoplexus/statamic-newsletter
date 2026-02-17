<?php

namespace Xenoplexus\StatamicNewsletter\Tags;

use Statamic\Tags\Tags;

class NewsletterFormTag extends Tags
{
    protected static $handle = 'newsletter_form';

    public function index(): string
    {
        $prefix = config('statamic-newsletter.routes.prefix', 'newsletter');

        return view('statamic-newsletter::form', [
            'action' => url("/{$prefix}/subscribe"),
        ])->render();
    }
}
