<?php

use Illuminate\Support\Facades\Route;
use Xenoplexus\StatamicNewsletter\Http\Controllers\NewsletterController;

Route::get('/newsletter-preview/{slug}', [NewsletterController::class, 'preview'])
    ->name('newsletter.preview');
