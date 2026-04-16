# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v1.0.0-alpha.1] - 2026-04-16

### Added
- **Core Localization Framework**:
    - `LocalizationManager`: Orchestrates locale detection and synchronization.
    - `LocalizationState`: Thread-safe in-memory state management.
    - `LocalizationContext`: Integration with Laravel's `Context` for concurrent request safety (Octane).
    - `LocalizationConfig`: Centralized configuration handling.
- **Locale Drivers**:
    - `UrlDriver`: Extracts locale from URL segments.
    - `SessionDriver`: Manages locale via Laravel sessions.
    - `CookieDriver`: Persists locale in browser cookies.
    - `RequestPreferredLocaleDriver`: Automatically detects locale from browser headers (`Accept-Language`).
    - `RefererDriver`: Detects locale from referer headers, perfecting Livewire and AJAX support.
- **Routing & Redirection**:
    - `LocalizationRedirector`: Intelligent redirection to localized URLs.
    - "Ignore Paths" support to exclude specific routes (e.g., APIs, Webhooks) from localization logic.
    - "Double Registration" support for stable routing in complex applications.
- **Queue Support**:
    - `LocalizationQueueMiddleware`: Automatically captures and restores locale context in queued jobs.
    - `LocalizedJob` trait and `LocalizationAwareJob` interface for seamless background task localization.
- **Livewire Integration**:
    - `LivewireLocalizationBridge`: Middleware to maintain locale consistency across Livewire components and requests.
- **Testing Suite**:
    - Comprehensive unit and feature tests using Pest (84 tests, 165 assertions).
    - Mocks and stubs for driver isolation.
- **Developer Tools**:
    - Laravel Pint for code style enforcement.
    - PHPStan for static analysis.
    - GitHub Actions CI for automated testing and linting.

### Fixed
- Improved `UrlDriver` reliability by ensuring locales are only extracted when explicitly supported.
- Fixed `LocalizationQueueMiddleware` signature to correctly handle job and closure types.
- Resolved naming inconsistencies between `URLParser` and its interface.
- Fixed configuration fallback logic for cookie expiration and other optional keys.

### Changed
- Standardized architectural naming to `URLParserInterface`.
- Optimized service provider bindings for better container performance.
- Centralized all package configuration into `octane-localization.php`.

### Removed
- Integration tests for specific runtimes (native, context, swoole-concurrent, open-swoole-concurrent) to focus on a unified, high-quality core test suite.

