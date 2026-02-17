<?php

use Illuminate\Support\Facades\Route;
use Xenoplexus\StatamicNewsletter\Http\Controllers\NewsletterController;
use Xenoplexus\StatamicNewsletter\Http\Middleware\VerifyPostmarkWebhook;

$prefix = config('statamic-newsletter.routes.prefix', 'newsletter');
$webhookPrefix = config('statamic-newsletter.routes.webhook_prefix', 'webhook/postmark');

Route::post("/{$prefix}/subscribe", [NewsletterController::class, 'subscribe'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
    ->name('newsletter.subscribe');

Route::get("/{$prefix}/unsubscribe/{email}", [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

Route::get("/{$prefix}/unsubscribe", [NewsletterController::class, 'unsubscribePage'])
    ->name('newsletter.unsubscribe.page');

Route::post("/{$prefix}/unsubscribe", [NewsletterController::class, 'unsubscribeByEmail'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
    ->middleware('throttle:10,1')
    ->name('newsletter.unsubscribe.form');

Route::post("/{$webhookPrefix}/suppression", [NewsletterController::class, 'handleSuppressionWebhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
    ->middleware([VerifyPostmarkWebhook::class, 'throttle:60,1'])
    ->name('webhook.postmark.suppression');
