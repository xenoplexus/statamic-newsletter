# Changelog

All notable changes to this package will be documented in this file.

## [1.0.3] - 2026-02-17

### Added
- `.gitattributes` to exclude tests/docs from Composer downloads
- `CHANGELOG.md` for version history
- `homepage` and `support` URLs in composer.json

### Changed
- `minimum-stability` from `dev` to `stable`

## [1.0.2] - 2026-02-17

### Added
- Author info in composer.json for Statamic CP display

## [1.0.1] - 2026-02-17

### Fixed
- CSRF exceptions now auto-registered via ServiceProvider — no manual `bootstrap/app.php` configuration needed

## [1.0.0] - 2026-02-17

### Added
- Newsletter subscription and unsubscription with signed URL verification
- Welcome email on subscribe via Postmark transactional stream
- Queued batch sending via Postmark broadcast stream
- Publish-to-send workflow (publish a Statamic entry to trigger send)
- Postmark suppression webhook support
- `{{ newsletter_form }}` Antlers tag for embedding subscribe forms
- `newsletter:preview` Artisan command with `--demo` flag
- Configurable route prefixes, database connection, Postmark streams
- Publishable email templates and static assets
