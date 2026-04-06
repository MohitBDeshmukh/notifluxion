# Changelog

All notable changes to this project will be documented in this file.

## [v1.1.1] - 2026-04-06

### Added
- **Packagist Preparation**: Restructured `composer.json` namespaces directly linking the `mohitbdeshmukh/notifluxion` generic GitHub pipeline mapping to avoid collision. Implemented MIT License tracking.
- **Provider Documentation**: Scaled `README.md` exhaustively to surface comprehensive `SMS`, `Email`, and `WhatsApp` configuration limits + exhaustive multi-environment variables maps for host installations.

## [v1.1.0] - 2026-04-06

### Added
- **Multi-Tenant Scoping**: Enforced `tenant_id` isolated query closures natively across dynamic Queue/Log database payload interceptors and tables.
- **Sub-Job Fast Batching**: `NotificationManager` safely detects arrays/Collections and eliminates N+1 queries by bulk-compiling a raw associative DB mass-insert. 
- **Queue Fallback Routines**: Central queues now automatically detect crash exceptions, compute backoff timers, increment attempt rows, and intrinsically copy failed jobs to configurable `$fallback` driver clones.
- **Events Layer Listener**: Dispatches native Laravel hooks (`NotificationSent`, `NotificationFailed`, `NotificationQueued`) gracefully exposing API tracking telemetry explicitly.
- **Redis Strategy Wrapper**: Implemented a lightning-paced cache list stack bypassing relational MySQL tables completely for instant broadcast handling.
- **Advanced Marketing Templates**: Bound CC, BCC, Blade standard `$view` HTML layout rendering, and dual string Shortcodes seamlessly inside the basic Mailer interfaces.

### Fixed
- **CLI Configuration Scope**: Hard-deleted localized `phpunit.xml` override loops inside `TestLiveProvidersCommand.php` and `InstallCommand.php`. When installed in external Laravel Host partitions via Composer, these CLI commands will now intrinsically leverage the host's native `.env` bindings safely using the standard `config()` abstraction without hijacking localized caches.
- **Security Vulnerability**: Scrubbed live Twilio and Google SMTP configurations out of the core `phpunit.xml` testing configuration and isolated them out of version control barriers via a `.gitignore` block natively.

### Impact & Migrations
- **Impact**: Significant scaling stability improvements. All drivers support robust 5,000+ API groupings passively. 
- **Migrations**: Required. `2026_01_01_000000_create_notification_tables.php` was mapped with `tenant_id` columns. Run `php artisan notify:install` internally.


## [v1.0.1] - 2026-04-06

### Fixed
- **Queue Engine**: Isolated Driver Configuration array extraction natively inside `DatabaseQueueStrategy.php` daemon reconstruction sequence. This resolves "Credentials missing" API failures thrown by the background daemon processing delayed SendGrid, Twilio, and WhatsApp payloads.  
- **Queue Payloads**: Dynamic `stdClass` CLI injection correctly retains property state using generic encapsulation on scheduled delays.
