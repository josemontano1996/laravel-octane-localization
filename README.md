# Laravel Octane Localization

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/josemontano1996/laravel-octane-localization/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/josemontano1996/laravel-octane-localization/actions)

A state-aware, high-performance localization package for Laravel, meticulously designed for **Laravel Octane** (also compatible with traditional FPM). 

In long-lived worker environments like Octane, managing request state is tricky. This package solves the "leaking locale" problem by using scoped container bindings, ensuring every request gets its own correctly detected and isolated localization state.

## The Problem with Octane

Traditional localization packages often rely on Singletons or global application state to remember the requested language. In **Laravel Octane**, the application boots once and stays in memory. If a user requests a page in Spanish (`/es`), a Singleton remembers "Spanish". If the next user requests the homepage (`/`), they might see it in Spanish because the state **leaked** between requests.

This package was built from the ground up to solve this by utilizing Laravel's **scoped container bindings**, ensuring that localization state is isolated per-request and safely flushed immediately after the response is sent.

## Key Features

- 🏎️ **Octane Ready**: Zero state-leakage between requests.
- 🛠️ **Extensible Drivers**: Detect locale from URL, Session, Cookies, or Browser headers.
- 🌊 **Livewire Support**: Seamlessly syncs localization for Livewire components.
- 📂 **Queue Support**: Re-hydrates localization context in queued jobs via a simple interface.
- 🎨 **Blade Directives**: Built-in directives for easy UI localization.
- 🚀 **SEO Ready**: Generates `hreflang` alternate links with a single directive.
- ⚡ **Performance First**: Minimal overhead with optimized detection logic.

---

## Installation

You can install the package via composer:

```bash
composer require josemontano1996/laravel-octane-localization
```

You should publish the config file with:

```bash
php artisan vendor:publish --tag="localization-config"
```

---

## Configuration

The published `config/octane-localization.php` file allows you to define your supported locales and the order of drivers used for detection.

```php
return [
    'supported_locales' => ['en', 'es', 'fr'],
    'parameter_key' => 'locale',
    
    'drivers' => [
        \Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver::class,
        \Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
        \Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
    ],
    
    'redirections' => [
        'active' => true,
        'except' => ['api/*', 'webhooks/*'],
    ],
];
```

---

## Usage

### Middleware Setup

To enable automatic detection, add the `LocalizationMiddleware` to your `web` middleware group in `app/Http/Kernel.php` (or `bootstrap/app.php` for Laravel 11+).

> [!IMPORTANT]
> **Middleware Order Matters!**
> 
> The middleware must run:
> 1. **After** `StartSession` (if using the Session driver).
> 2. **Before** `SubstituteBindings` (to ensure route parameters are resolved with the correct locale).

```php
// Laravel 11+ Example (bootstrap/app.php)
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(prepend: [
        \Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddleware::class,
    ]);
})
```

### Route Macro (`localizedWithPrefix`)

Instead of manually defining prefixes and middleware, use the provided `localizedWithPrefix` macro:

```php
Route::localizedWithPrefix(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```
This automatically prefixes the group with the `parameter_key` (e.g., `/{locale}`) and applies the `LocalizationMiddleware`.

### Blade Directives

We provide developer-friendly Blade directives to streamline your views:

| Directive | Description | Example |
|---|---|---|
| `@currentLocale` | Outputs the current application locale | `Lang: @currentLocale` |
| `@isLocale('en')` | Conditional block. Supports `@elseisLocale` and `@else` | `@isLocale('es') ¡Hola! @else Hello! @endisLocale` |
| `@supportedLocales` | Iterates over all supported locales | `@supportedLocales($code, $data) {{ $code }} @endsupportedLocales` |
| `@localizedUrl('es')` | Returns the current URL in another locale | `<a href="@localizedUrl('es')">Spanish</a>` |
| `@alternateLinks` | Generates SEO `hreflang` tags (see below) | `<head> @alternateLinks </head>` |

#### Language Switcher Example

Combining these directives makes building a language switcher effortless:

```blade
<div class="language-switcher">
    <span>Current: @currentLocale</span>
    <ul>
        @supportedLocales($code, $data)
            @isLocale($code)
                <li><strong>{{ $data['name'] ?? $code }} (Active)</strong></li>
            @else
                <li><a href="@localizedUrl($code)">{{ $data['name'] ?? $code }}</a></li>
            @endisLocale
        @endsupportedLocales
    </ul>
</div>
```

### SEO Alternate Links

Proper international SEO requires telling search engines about the localized versions of your pages. You can automatically generate `<link rel="alternate" hreflang="...">` tags for your `<head>` using:

```blade
<head>
    <title>My App</title>
    @alternateLinks
</head>
```
*This automatically includes the `x-default` tag pointing to your application's default fallback locale.*

---

## The Localization Lifecycle

Understanding how the `LocalizationManager` operates behind the scenes will help you master the package. The lifecycle involves three main steps during a request:

1. **`detect(Request $request)`**: The manager runs through your configured driver stack to find a supported locale. Once found, it saves it in the scoped `LocalizationState`. crucially, it calls `storeLocale()` on all primary drivers so they can persist it (e.g., saving to a session or cookie).
2. **`syncWithApplication()`**: The manager pushes the detected locale out to the rest of the framework. It automatically updates:
    - `App::setLocale()`
    - `URL::defaults()` (so future generated URLs automatically have the correct prefix)
    - `Carbon::setLocale()` (if installed)
    - `Number::useLocale()` (if installed)
3. **`flush()`**: Essential for Octane! After the response is sent to the browser, the manager completely resets the state, URL defaults, and framework facades back to the system default, ensuring **zero state leakage** for the next worker request.

---

## Advanced Features

### Livewire Integration

The package is **100% Livewire safe by default**. You do not need to configure anything, add special traits to your components, or register middleware for Livewire. We automatically register a `LivewireLocalizationBridge` that seamlessly handles localization persistence and state syncing during all Livewire AJAX requests.

### How Drivers & Livewire Interact

To understand why this is powerful, it helps to understand how the package handles standard vs. Livewire requests:

#### 1. Standard HTTP Requests
When a user visits a traditional route (e.g., `/es/dashboard`), the `LocalizationMiddleware` kicks in. It iterates through the standard `drivers` defined in your config (usually `UrlDriver` -> `SessionDriver`). 
- The `UrlDriver` sees the `/es/` in the URL, successfully detects "Spanish", and then **persists** this locale to your session (or cookies) so the application "remembers" it.

#### 2. The Livewire Problem
Livewire operates by sending background AJAX POST requests to a central endpoint (usually `/livewire/update`). **This endpoint does not have a locale prefix in its URL.**
If Livewire used the standard `UrlDriver`, it would fail to find a locale in `/livewire/update` and would quickly reset your application to the default language right in the middle of a user's interaction!

#### 3. The Solution (`ext.livewire.drivers`)
The `LivewireLocalizationBridge` intercepts these specific AJAX requests. Instead of using the standard driver stack, it intelligently switches to an isolated `ext.livewire.drivers` stack defined in `config/octane-localization.php`:

```php
'ext' => [
    'livewire' => [
        'drivers' => [
            \Josemontano1996\LaravelOctaneLocalization\Drivers\RefererDriver::class,
            \Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
            \Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
        ]
    ],
],
```

- **`RefererDriver` (The Hero)**: Because the Livewire AJAX request originates from a page the user is currently viewing, it sends a `Referer` header (e.g., `Referer: https://yoursite.com/es/dashboard`). The `RefererDriver` intercepts this header, extracts the `/es/`, and successfully restores the Spanish locale for the Livewire component.
- **`SessionDriver` (The Fallback)**: If the referer is missing for any reason, the `SessionDriver` catches it, pulling the language exactly as it was persisted during the very first standard HTTP request.

### Queued Jobs (Restore & Reset Pattern)

When a job is pushed to the queue, the current localization "context" needs to travel with it. We handle this using a **Restore & Reset** pattern to ensure that the worker's state is correctly set before the job runs and safely cleaned up afterward.

To enable this:
1. Implement the `LocalizationAwareJob` contract.
2. Use the `LocalizedJob` trait (which provides the implementation).
3. Add the `LocalizationQueueMiddleware` to the job.

#### How it works
The `LocalizationQueueMiddleware` detects if a job is "localization aware" and performs the following lifecycle:
- **Before execution**: It calls `restoreLocalization()`, which pulls the locale from the Laravel 11 Context and syncs it with the worker's application state.
- **After execution (using `try...finally`)**: It calls `resetLocalization()`, which flushes the worker's state back to the system default, preventing any "locale leakage" to the next job in the queue.

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Josemontano1996\LaravelOctaneLocalization\Contracts\Support\LocalizationAwareJob;
use Josemontano1996\LaravelOctaneLocalization\Queue\LocalizationQueueMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Traits\LocalizedJob;

class ProcessOrder implements ShouldQueue, LocalizationAwareJob
{
    use LocalizedJob;

    public function middleware(): array
    {
        return [new LocalizationQueueMiddleware()];
    }

    public function handle()
    {
        // This runs with the original request's locale!
    }
}
```

---

## Custom Drivers

You can easily create custom drivers by implementing the `LocaleDriverInterface`.

```php
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Illuminate\Http\Request;

class MyCustomDriver implements LocaleDriverInterface
{
    public function getLocale(Request $request): ?string
    {
        return $request->user()?->preferred_locale;
    }

    public function storeLocale(string $locale, Request $request): void
    {
        // Example: Save the detected locale back to the user's profile
        if ($user = $request->user()) {
            $user->update(['preferred_locale' => $locale]);
        }
    }
}
```

### Read-Only Drivers (The "No-Op" Pattern)

Sometimes you want a driver to *detect* a locale (e.g., from a specialized API header or a Bot User-Agent) but you want to prevent the system from trying to "save" that locale back into the session or cookies.

For these cases, you should use the `DoesNotPersistLocale` trait. This trait provides an empty implementation for `storeLocale`, effectively turning your driver into a **No-Op** for persistence:

```php
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;
use Illuminate\Http\Request;

class BotDetectionDriver implements LocaleDriverInterface
{
    use DoesNotPersistLocale; // Automatically fulfills the interface with a No-Op

    public function getLocale(Request $request): ?string
    {
        // Detection logic here...
    }
}
```

---

## Testing

Run the tests with:

```bash
composer test
```
*(Uses Pest PHP under the hood)*

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.