---
name: octane-localization
description: Guidelines and patterns for working with the laravel-octane-localization package to implement state-safe, high-performance localization for Laravel Octane.
---

# Laravel Octane Localization

## When to use this skill
Use this skill when initializing localized routes, adding multi-language support to blade views, setting up SEO tags, dispatching localized jobs, or creating custom locale detection drivers.

This package provides state-aware, high-performance localization for Laravel, meticulously designed for **Laravel Octane** (also compatible with traditional FPM).

### Core Concepts

**Memory Safety with Octane:**
Unlike other packages, this package relies on **scoped container bindings** for stateful context and **singletons** for stateless helpers. This design isolated localization state per-request, meaning you get zero state-leakage between worker requests without requiring deep Coroutine contexts or sacrificing performance.

### Key Features / Example Usage

**1. Localized Routing (Important!)**

Whenever you need to group routes that should be localized and prefixed with a locale, use the provided Route macro `localizedWithPrefix`. This automatically applies the necessary middleware.

@verbatim
<code-snippet name="Defining Localized Routes" lang="php">
use Illuminate\Support\Facades\Route;

// Recommended: This automatically applies the `{locale}` prefix and the `LocalizationMiddleware`
Route::localizedWithPrefix(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
</code-snippet>
@endverbatim

**2. Blade Directives**

Use built-in directives instead of raw PHP for common localization tasks, conditional checking, and international SEO tags.

@verbatim
<code-snippet name="Blade Usage" lang="html">
<!-- Outputs active locale -->
<html lang="@currentLocale">

<!-- Conditional Locale Checking -->
@isLocale('en') 
    Hello! 
@else 
    ¡Hola! 
@endisLocale

<!-- Supported Locales Loop (e.g., for language switchers) -->
@supportedLocales($code, $data)
    <a href="@localizedUrl($code)">{{ $data['name'] ?? $code }}</a>
@endsupportedLocales

<head>
    <!-- SEO / Alternate Hreflang Links -->
    @alternateLinks
</head>
</code-snippet>
@endverbatim

@verbatim
// No manual configuration is required for Livewire components, the package automatically handles it.
// The LivewireLocalizationBridge automatically intercepts the 'Referer' header and restores component contexts.
@endverbatim

**3. Queued Jobs**

If a dispatched Job needs to execute with the same locale as the HTTP request that triggered it, it MUST implement the Restore & Reset pattern.

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
        // Executes in the correct, isolated worker locale
    }
}
</code-snippet>
@endverbatim

**4. Custom Drivers**

When creating a new way to detect language, implement `LocaleDriverInterface`. Use the `DoesNotPersistLocale` trait for read-only detection mechanisms like Bots or API headers.

@verbatim
<code-snippet name="Custom Read-Only Driver" lang="php">
namespace App\Localization\Drivers;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;

class ApiHeaderDriver implements LocaleDriverInterface
{
    // Automatically fulfills the storage requirement with a No-Op
    use DoesNotPersistLocale;

    public function getLocale(Request $request): ?string
    {
        return $request->header('X-Locale');
    }
}
</code-snippet>
@endverbatim

### When to use this package's features:
- If asked to create localized routes, always group them under `Route::localizedWithPrefix()`.
- If asked to build language dropdowns or conditional views, use `@supportedLocales`, `@localizedUrl()`, and `@isLocale()`.
- If asked to ensure background queued classes run in the requested language, implement `LocalizationAwareJob`.
- If asked to implement a new method of detecting language (e.g. specialized API headers), create a Custom Driver.
