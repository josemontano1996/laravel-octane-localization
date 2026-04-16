# Laravel Octane Localization

[![GitHub Tests Action Status](https://github.com/josemontano1996/laravel-octane-localization/actions/workflows/run-tests.yml/badge.svg)](https://github.com/josemontano1996/laravel-octane-localization/actions)


A state-aware, high-performance localization package for Laravel, meticulously designed for **Laravel Octane** (also compatible with traditional FPM). 

In long-lived worker environments like Octane, managing request state is tricky. This package solves the "leaking locale" problem by using scoped container bindings, ensuring every request gets its own correctly detected and isolated localization state.

## The Problem with Octane

Traditional localization packages often rely on Singletons or global application state to remember the requested language. In **Laravel Octane**, the application boots once and stays in memory. If a user requests a page in Spanish (`/es`), a Singleton remembering "Spanish" would cause the next user to see the homepage in Spanish because the state **leaked**.

This package prevents state leakage by combining **scoped container bindings** and **singletons** efficiently:
- **Stateful** components (like the detected Locale or current Request Context) are bound using Scoped bindings, giving each worker request a fresh, isolated state that is safely flushed.
- **Stateless** components (like URL parsers and Helpers) remain Singletons to minimize memory overhead and maximize maximum performance, ensuring your Octane workers run at peak efficiency.

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
php artisan vendor:publish --tag="octane-localization"
```

---

## Configuration

The published `config/octane-localization.php` file allows you to define your supported locales and the order of drivers used for detection.

```php
return [
    'parameter_key' => 'locale',
    'supported_locales' => ['en', 'es', 'fr'],
    'cookie_ttl' => 1440,
    
    'drivers' => [
        \Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver::class,
        \Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
        \Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
    ],
    
    'redirections' => [
        'active' => true,
        'except' => ['api/*', 'webhooks/*'],
    ],

    'ext' => [
        'livewire' => [
            'drivers' => [
                \Josemontano1996\LaravelOctaneLocalization\Drivers\RefererDriver::class,
                \Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
                \Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
            ],
        ],
    ],
];
```

---

## Usage

### 1. Route Macro (`localizedWithPrefix`) (Recommended)

The easiest way to set up localized routes is using the provided `localizedWithPrefix` macro. It automatically applies the `parameter_key` prefix (e.g., `/{locale}`) and attaches the `LocalizationMiddleware` for you.

```php
Route::localizedWithPrefix(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```
*Note: Because this macro automatically applies the `LocalizationMiddleware`, you do not need to register it globally.*

### 2. Global Middleware Setup (Alternative)

If you prefer to have the application automatically detect the locale for *all* routes without requiring a URL prefix (e.g., relying solely on Sessions or Cookies), you can add `LocalizationMiddleware` globally to your `web` middleware group. **Do not do this if you are exclusively using `Route::localizedWithPrefix`, as it will cause the middleware to run twice!**

To enable automatic detection globally, add the `LocalizationMiddleware` to your `web` middleware group in `app/Http/Kernel.php` (or `bootstrap/app.php` for Laravel 11+).

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

The package features **smart Livewire support**. If it detects Livewire in your project, it seamlessly handles localization persistence and syncing during Livewire AJAX updates. **Crucially, it does not require Livewire as a composer dependency**, meaning it works flawlessly whether your project uses Livewire or not.

When a Livewire AJAX payload is sent, standard URL drivers fail because the request goes to `/livewire/update` (missing the locale prefix). Our `LivewireLocalizationBridge` automatically intercepts these requests and switches to an isolated `ext.livewire.drivers` stack defined in your config.

1. **`RefererDriver`**: It intercepts the `Referer` header (e.g., `https://yoursite.com/es/dashboard`), extracts the `/es/`, and restores the locale for the component.
2. **`SessionDriver` / `CookieDriver`**: If the referer is missing, it falls back to your persisted storage perfectly.

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

Create custom drivers by implementing the `LocaleDriverInterface`. 
- `getLocale(Request)`: Contains the logic to read/detect the locale.
- `storeLocale(string, Request)`: Contains the logic to persist the locale (e.g., to a session or database) so it can be remembered on subsequent requests without a prefix.

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
        if ($user = $request->user()) {
            $user->update(['preferred_locale' => $locale]);
        }
    }
}
```

### Read-Only Drivers (The "No-Op" Trait)

Sometimes you want a driver to *detect* a locale (e.g., from an API header, Bot User-Agent, or URL segment) but you **don't** want it to store or persist that locale.

You can use the `DoesNotPersistLocale` trait to automatically fulfill the `storeLocale` requirement with a blank "No-Op" method. This ensures no locale storage is needed for the driver:

```php
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;
use Illuminate\Http\Request;

class BotDetectionDriver implements LocaleDriverInterface
{
    use DoesNotPersistLocale;

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