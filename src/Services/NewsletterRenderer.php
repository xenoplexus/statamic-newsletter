<?php

namespace Xenoplexus\StatamicNewsletter\Services;

use Illuminate\Support\Str;
use Statamic\Entries\Entry;

class NewsletterRenderer
{
    public static function render(Entry $entry): string
    {
        $title = e($entry->get('title'));
        $introHeading = e($entry->get('intro_heading') ?? 'Hi there,');
        $introBody = e($entry->get('intro_body') ?? '');
        $date = $entry->date()?->format('F Y') ?? now()->format('F Y');

        $raw = $entry->get('sections');
        $rawSections = is_array($raw) ? $raw : (is_iterable($raw) ? iterator_to_array($raw) : []);

        $sections = collect($rawSections)
            ->filter(fn ($s) => ($s['type'] ?? '') === 'content_section')
            ->map(fn ($s) => [
                'heading' => e($s['heading'] ?? ''),
                'body' => Str::markdown($s['body'] ?? ''),
                'image' => isset($s['image']) ? self::resolveImageUrl($s['image']) : null,
            ])
            ->values()
            ->all();

        return view('statamic-newsletter::emails.newsletter', array_merge(
            self::templateData(),
            [
                'title' => $title,
                'date' => $date,
                'intro_heading' => $introHeading,
                'intro_body' => $introBody,
                'sections' => $sections,
            ]
        ))->render();
    }

    public static function templateData(): array
    {
        $cfg = config('statamic-newsletter');

        return [
            'org_name' => $cfg['organization']['name'] ?? '',
            'org_address' => $cfg['organization']['address'] ?? '',
            'org_email' => $cfg['organization']['email'] ?? '',
            'org_url' => rtrim($cfg['organization']['url'] ?? config('app.url'), '/'),
            'org_tagline' => $cfg['organization']['tagline'] ?? '',
            'org_copyright' => $cfg['organization']['copyright'] ?? '',
            'banner_image' => $cfg['images']['banner'] ?? '',
            'from_address' => $cfg['email']['from_address'] ?? '',
            'route_prefix' => $cfg['routes']['prefix'] ?? 'newsletter',
        ];
    }

    public static function renderDemo(): string
    {
        return view('statamic-newsletter::emails.newsletter', array_merge(
            self::templateData(),
            [
                'title' => 'The Autumn Dispatch',
                'date' => now()->format('F Y'),
                'intro_heading' => 'Hello, reader!',
                'intro_body' => 'This is a design preview with placeholder content. When you publish a real newsletter entry, your content will appear here instead.',
                'sections' => [
                    [
                        'heading' => 'Sed ut perspiciatis unde omnis',
                        'body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>',
                        'image' => null,
                    ],
                    [
                        'heading' => 'Nemo enim ipsam voluptatem',
                        'body' => '<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>',
                        'image' => null,
                    ],
                ],
            ]
        ))->render();
    }

    private static function resolveImageUrl($image): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        if (is_array($image)) {
            $path = $image[0] ?? '';
        } else {
            $path = (string) $image;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $diskUrl = rtrim(config('filesystems.disks.assets.url', '/assets'), '/');
        $path = $diskUrl.'/'.ltrim($path, '/');

        return $baseUrl.$path;
    }
}
