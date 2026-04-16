# Laravel Octane Localization

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/josemontano1996/laravel-octane-localization/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/josemontano1996/laravel-octane-localization/actions)

A state-aware, high-performance localization package for Laravel, meticulously designed for **Laravel Octane**. 

In long-lived worker environments like Octane, managing request state is tricky. This package solves the "leaking locale" problem by using scoped container bindings, ensuring every request gets its own correctly detected and isolated localization state.

## Key Features

- 🏎️ **Octane Ready**: Zero state-leakage between requests.
- 🛠️ **Extensible Drivers**: Detect locale from URL, Session, Cookies, or Browser headers.
- 🌊 **Livewire Support**: Seamlessly syncs localization for Livewire components.
- 📂 **Queue Support**: Automatically re-hydrates localization context in queued jobs.
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

The published `config/localization.php` file allows you to define your supported locales and the order of drivers used for detection.

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

### URL Routes

Define your routes with the locale parameter:

```php
Route::group(['prefix' => '{locale}'], function () {
    Route::get('/', function () {
        return view('welcome');
    });
});
```

---

## Advanced Features

### Livewire Integration

The package provides **out-of-the-box compatibility with Livewire**. It automatically registers a `LivewireLocalizationBridge` that handles localization persistence during Livewire components' AJAX requests.

Because Livewire requests often lack the locale prefix in their request URL, the bridge uses a specialized driver stack defined in the `ext` config section.

#### The `ext` Configuration

The `ext` (extensions) key in your `config/localization.php` allows you to define different driver stacks for specific contexts.

```php
'ext' => [
    'livewire' => [
        'drivers' => [
            \Josemontano1996\LaravelOctaneLocalization\Drivers\RefererDriver::class, // Crucial for Livewire
            \Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
            \Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
        ]
    ],
],
```

- **`RefererDriver`**: This is particularly useful for Livewire, as it can extract the locale from the `Referer` header of the AJAX request, ensuring the component remains in the same language as the page it's embedded in.
- **Context Isolation**: By separating Livewire drivers from standard HTTP drivers, you can ensure that detection logic is optimized for each request type.

### Queued Jobs

To ensure your queued jobs use the same locale as the request that dispatched them:

1. Use the `LocalizationQueueMiddleware`.
2. Implement the `LocalizationAwareJob` contract.

```php
use Josemontano1996\LaravelOctaneLocalization\Contracts\Support\LocalizationAwareJob;
use Josemontano1996\LaravelOctaneLocalization\Queue\LocalizationQueueMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Traits\HasLocalizationContext;

class ProcessOrder implements ShouldQueue, LocalizationAwareJob
{
    use HasLocalizationContext;

    public function middleware(): array
    {
        return [new LocalizationQueueMiddleware()];
    }

    public function handle()
    {
        // Job runs with the original request's locale
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
        // Persist logic...
    }
}
```

---

## Testing

Coming soon...

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.