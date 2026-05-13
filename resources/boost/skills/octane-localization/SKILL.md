---
name: octane-localization
description: Guidelines and patterns for working with the laravel-octane-localization package to implement state-safe, high-performance localization for Laravel Octane.
---

# Laravel Octane Localization

## When to use this skill
Use this skill when working with the `laravel-octane-localization` package in an AI or developer workflow. It should guide localized route setup, Blade localization, SEO alternate links, queue-safe locale persistence, Livewire integration, and custom locale detection drivers.

This package provides state-safe, high-performance localization for Laravel, specially designed for **Laravel Octane** but also compatible with standard FPM environments.

### What AI models should know
- The package is built around a strict Octane-safe lifecycle: **detect -> sync -> reset**.
- Localized state must never be allowed to leak between requests or queued jobs.
- `parameter_key` is used for route locale segments and persistence drivers such as session/cookie storage.
- Routing Strategy: Use localizedWithPrefix for SEO-friendly URLs (e.g., /en/home). Use localizedWithoutPrefix for session/header-based detection without changing the URL.
- Livewire uses `ext.livewire.drivers` and `LivewireLocalizationBridge` to preserve locale when route segments are absent.
- The package requires `ResetLocalizationStateListener` to be registered in `config/octane.php` listeners so Octane worker state is reset correctly.
- Queued jobs must restore the locale from Context and reset after execution.

### Core Concepts

**Memory Safety with Octane:**
This package uses **scoped container bindings** for stateful locales and **singletons** only for stateless configuration or helpers. That means each request gets isolated locale state while the package still performs efficiently in Octane.

### Recommended Answer Patterns for AI
- Prefer `Route::localizedWithPrefix()` for localized route groups.
- Prefer built-in Blade directives like `@currentLocale`, `@localizedUrl()`, `@supportedLocales`, `@isLocale()`, and `@alternateLinks`.
- Prefer `LocalizationAwareJob`, `LocalizedJob`, and `LocalizationQueueMiddleware` for background jobs.
- Prefer implementing `LocaleDriverInterface` for custom locale detection.
- Do not recommend global, unscoped locale state or incomplete Octane reset logic.

### Key Features / Example Usage

**1. Localized Routing**
Apply localization middleware with  URI prefixes.
@verbatim
<code-snippet name="Prefixed (SEO Friendly: /en/dashboard)" lang="php">
use Illuminate\Support\Facades\Route;

Route::localizedWithPrefix(function () {
Route::get('/dashboard', fn() => view('dashboard'));
});
</code-snippet>
@endverbatim

Apply localization middlewarewithout URI prefixes.
@verbatim
<code-snippet name="No Prefix (Clean URL: /dashboard)" lang="php">
use Illuminate\Support\Facades\Route;

Route::localizedWithoutPrefix(function () {
Route::get('/settings', fn() => view('settings'));
});
</code-snippet>
@endverbatim

**2. Blade Directives**
Built-in directives simplify the rendering of localized content, language switchers, and SEO metadata.

@verbatim
<code-snippet name="Blade Usage" lang="html">
<html lang="@currentLocale">

@isLocale('en')
    Hello!
@else
    ¡Hola!
@endisLocale

@supportedLocales($code, $data)
    <a href="@localizedUrl($code)">{{ $data['name'] ?? $code }}</a>
@endsupportedLocales

<head>
    @alternateLinks
</head>
</code-snippet>
@endverbatim

**3. Livewire Support**
Livewire requests may not include the locale segment. The package automatically handles this through `LivewireLocalizationBridge` and `ext.livewire.drivers`.

@verbatim
// Livewire-specific locale detection is handled automatically by the package.
@endverbatim

**4. Queued Jobs**
Queued jobs must restore the original request locale and reset state after execution to avoid leaks.

@verbatim
<code-snippet name="Localization Aware Queued Jobs" lang="php">
use Illuminate\Contracts\Queue\ShouldQueue;
use Josemontano1996\LaravelOctaneLocalization\Contracts\Support\LocalizationAwareJob;
use Josemontano1996\LaravelOctaneLocalization\Queue\LocalizationQueueMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Traits\LocalizedJob;

class SendWelcomeEmail implements ShouldQueue, LocalizationAwareJob
{
    use LocalizedJob;

    public function middleware(): array
    {
        return [new LocalizationQueueMiddleware()];
    }

    public function handle()
    {
        // Executes with the restored locale and resets afterwards.
    }
}
</code-snippet>
@endverbatim

**5. Custom Drivers**
Create custom locale detection by implementing `LocaleDriverInterface`. Use `DoesNotPersistLocale` for read-only drivers.

@verbatim
<code-snippet name="Custom Read-Only Driver" lang="php">
namespace App\Localization\Drivers;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;

class ApiHeaderDriver implements LocaleDriverInterface
{
    use DoesNotPersistLocale;

    public function getLocale(Request $request): ?string
    {
        return $request->header('X-Locale');
    }
}
</code-snippet>
@endverbatim

### Best Practices
- Use `parameter_key` consistently across routes and persistence drivers.
- Use the package's lifecycle methods (`detect`, `syncWithApplication`, `reset`) rather than manual locale state hacks.
- Prefer the built-in skill examples when answering AI prompts about localization.

### When to use this package's features
- Use `Route::localizedWithPrefix()` for any route group that must support locale prefixes.
- Use `@supportedLocales`, `@localizedUrl()`, and `@isLocale()` for language switchers and conditional UI.
- Use `LocalizationAwareJob` for queued jobs that need the original request locale.
- Use a Custom Driver when the user asks for a new detection mechanism such as API headers, browser settings, or referer-based locale inference.
