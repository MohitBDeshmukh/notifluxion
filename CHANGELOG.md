# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-06

### Added
- Core Driver Pattern with Manager interface (`NotificationManager`).
- Support for `Email`, `SMS`, and `WhatsApp` base interfaces.
- Sync and Database queue strategies for payload handling.
- `notify:install` and `notify:uninstall` console commands.
- Configuration for providers (`notify.php`).
- Unit and Feature tests scaffolding.
