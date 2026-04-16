---
name: octane-localization
description: Guidelines and patterns for working with the laravel-octane-localization package to implement state-safe, high-performance localization for Laravel Octane.
---

# Laravel Octane Localization

## When to use this skill
Use this skill when initializing localized routes, adding multi-language support to blade views, setting up SEO tags, dispatching localized jobs, or creating custom locale detection drivers.

## Core Concepts

Always remember that this package isolates localization state per request using scoped bindings to prevent memory leaks in Octane. Never use global state singletons to remember user locales.

## Routing

Whenever you need to group routes that should be localized and prefixed with a locale, use the provided Route macro:

```php
use Illuminate\Support\Facades\Route;

Route::localizedWithPrefix(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```
*Note: This automatically applies the `LocalizationMiddleware`.*

## Blade Directives

Use these specific blade directives for localized UI rendering:

- **Check Current Locale**: `@currentLocale` outputs the active locale string.
- **Conditional Checking**: `@isLocale('en')` content `@else` alternative `@endisLocale`
- **Supported Locales Loop**: 
  ```blade
  @supportedLocales($code, $data)
      <a href="@localizedUrl($code)">{{ $data['name'] ?? $code }}</a>
  @endsupportedLocales
  ```
- **SEO/Alternate Links**: Simply place `@alternateLinks` inside your HTML `<head>` tag to generate `hreflang` metadata.

## Queued Jobs

If a dispatched Job needs to execute with the same locale as the HTTP request that triggered it, it MUST implement the Restore & Reset pattern. Add the trait, interface, and middleware:

```php
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
        // Executes in the correct locale
    }
}
```

## Custom Drivers

When creating a new way to detect language (e.g., from a specialized API headers), implement `LocaleDriverInterface`. 

If the driver only detects the language but doesn't need to persist it (like saving to Session), use the `DoesNotPersistLocale` trait to fulfill the storage logic as a NO-OP:

```php
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
```
