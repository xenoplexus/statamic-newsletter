<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    */
    'organization' => [
        'name' => env('NEWSLETTER_ORG_NAME', 'My Organization'),
        'address' => env('NEWSLETTER_ORG_ADDRESS', ''),
        'email' => env('NEWSLETTER_ORG_EMAIL', 'info@example.com'),
        'url' => env('NEWSLETTER_ORG_URL', env('APP_URL', 'https://example.com')),
        'tagline' => env('NEWSLETTER_ORG_TAGLINE', ''),
        'copyright' => env('NEWSLETTER_ORG_COPYRIGHT', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    */
    'email' => [
        'from_address' => env('NEWSLETTER_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'info@example.com')),
        'reply_to' => env('NEWSLETTER_REPLY_TO', env('MAIL_FROM_ADDRESS', 'info@example.com')),
        'noreply_address' => env('NEWSLETTER_NOREPLY_ADDRESS', 'no.reply@example.com'),
        'welcome_subject' => env('NEWSLETTER_WELCOME_SUBJECT', 'Welcome to our newsletter!'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Postmark
    |--------------------------------------------------------------------------
    */
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
        'webhook_token' => env('POSTMARK_WEBHOOK_TOKEN'),
        'welcome_tag' => env('NEWSLETTER_WELCOME_TAG', 'welcome'),
        'newsletter_tag' => env('NEWSLETTER_TAG', 'newsletter'),
        'welcome_stream' => env('NEWSLETTER_WELCOME_STREAM', 'outbound'),
        'broadcast_stream' => env('NEWSLETTER_BROADCAST_STREAM', 'broadcast'),
        'track_opens' => env('NEWSLETTER_TRACK_OPENS', true),
        'track_links' => env('NEWSLETTER_TRACK_LINKS', 'None'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('NEWSLETTER_DB_CONNECTION'),
        'table' => env('NEWSLETTER_DB_TABLE', 'newsletter_subscribers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sending
    |--------------------------------------------------------------------------
    */
    'sending' => [
        'batch_size' => env('NEWSLETTER_BATCH_SIZE', 50),
        'max_retries' => env('NEWSLETTER_MAX_RETRIES', 3),
        'retry_backoff' => env('NEWSLETTER_RETRY_BACKOFF', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Statamic
    |--------------------------------------------------------------------------
    */
    'statamic' => [
        'collection' => env('NEWSLETTER_COLLECTION', 'newsletters'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */
    'images' => [
        'banner' => env('NEWSLETTER_BANNER_IMAGE', '/images/banners/newsletter.jpg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => env('NEWSLETTER_ROUTE_PREFIX', 'newsletter'),
        'webhook_prefix' => env('NEWSLETTER_WEBHOOK_PREFIX', 'webhook/postmark'),
    ],

];
