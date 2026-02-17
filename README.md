# Statamic Newsletter

A Statamic addon for managing newsletters with Postmark integration. Subscribe/unsubscribe handling, welcome emails, queued batch sending, and suppression webhook support.

## Features

- **Subscribe/unsubscribe** with signed URL verification
- **Welcome email** sent automatically on subscribe
- **Batch sending** via Postmark with configurable batch sizes and queued jobs
- **Publish-to-send** workflow — publishing a newsletter entry in the CP dispatches it to all active subscribers
- **Postmark suppression webhook** automatically deactivates bounced/complained addresses
- **Antlers `{{ newsletter_form }}` tag** for embedding subscribe forms in any template
- **CLI preview** with `php artisan newsletter:preview` (supports `--demo` for placeholder content)
- **Fully configurable** via env variables — org info, Postmark credentials, DB connection, route prefixes
- **Publishable templates** for per-project email customization

## Requirements

- PHP 8.3+
- Statamic 6 / Laravel 12
- Postmark account (for sending)

## Installation

```bash
composer require xenoplexus/statamic-newsletter
php please newsletter:install
php artisan migrate
```

The install command copies the newsletter collection, blueprint, and unsubscribe page into your project.

## Configuration

Add these to your `.env`:

```env
# Required
POSTMARK_TOKEN=your-postmark-server-token
POSTMARK_WEBHOOK_TOKEN=your-webhook-auth-token

# Organization
NEWSLETTER_ORG_NAME="Your Organization"
NEWSLETTER_ORG_URL=https://example.com
NEWSLETTER_ORG_EMAIL=info@example.com
NEWSLETTER_ORG_ADDRESS="123 Main St, City, ST 12345"

# Email
NEWSLETTER_FROM_ADDRESS=info@example.com
NEWSLETTER_REPLY_TO=info@example.com
NEWSLETTER_NOREPLY_ADDRESS=no-reply@example.com
NEWSLETTER_WELCOME_SUBJECT="Welcome to our newsletter!"

# Optional
NEWSLETTER_ORG_TAGLINE="Your tagline here"
NEWSLETTER_ORG_COPYRIGHT="&copy; 2025 Your Org. All rights reserved."
NEWSLETTER_BANNER_IMAGE=/assets/images/banner.jpg
NEWSLETTER_TAG=newsletter
NEWSLETTER_WELCOME_TAG=welcome
NEWSLETTER_BATCH_SIZE=50
NEWSLETTER_DB_CONNECTION=mysql
NEWSLETTER_DB_TABLE=newsletter_subscribers
```

Publish the full config to customize further:

```bash
php artisan vendor:publish --tag=statamic-newsletter-config
```

## Usage

### Subscribe form

Use the Antlers tag in any template:

```antlers
{{ newsletter_form }}
```

Or build your own form that POSTs to `/{prefix}/subscribe` with an `email` field.

### Sending newsletters

1. Create a newsletter entry in the CP using the `newsletters` collection
2. Add an intro heading, optional intro body, and content sections
3. **Publish** the entry — it automatically dispatches to all active subscribers

To resend: unpublish, set `newsletter_send_count` to `0`, clear `newsletter_sent_at`, then republish.

### Preview

```bash
php artisan newsletter:preview              # Pick from existing entries
php artisan newsletter:preview my-slug      # Preview a specific entry
php artisan newsletter:preview --demo       # Preview with placeholder content
```

### Customizing email templates

```bash
php artisan vendor:publish --tag=statamic-newsletter-views
```

Templates are published to `resources/views/vendor/statamic-newsletter/` for editing.

### Webhook

Set up a Postmark suppression webhook pointing to:

```
https://yoursite.com/webhook/postmark/suppression
```

Include the `X-Webhook-Token` header matching your `POSTMARK_WEBHOOK_TOKEN` env var.

## License

MIT
