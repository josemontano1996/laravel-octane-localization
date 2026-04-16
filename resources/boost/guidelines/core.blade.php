When building features or modifying code in this project, you MUST adhere to the following core guidelines regarding localization and state management:

### 1. Octane Memory Safety (Strict No-State-Leaking)
Because this application is configured for **Laravel Octane**, it runs in a long-lived process. 
- NEVER store user-specific state (like the current Locale, Request context, or user preferences) in a shared Singleton.
- ALWAYS use Laravel's `$app->scoped(...)` container bindings for any service that interacts with the active request's localization state. 
- You can and should use Singletons for purely stateless services (such as helpers, URL parsers, and configuration loaders) to maximize performance.

### 2. Localization Over Defaults
- When generating URLs or links, do not hardcode prefixes or assume standard routing rules. Rely on the package's `Route::localizedWithPrefix()` macros for grouping.
- Before directly calling `app()->getLocale()` inside of background jobs or Livewire components, guarantee that the context has been restored. Specifically, any queue job must implement `LocalizationAwareJob` and use the `LocalizedJob` trait to safely transport the locale across Octane worker boundaries.

### 3. Blade Directives and Views
- Favor using the package's strict blade directives (`@currentLocale`, `@isLocale`, `@supportedLocales`, `@localizedUrl`) instead of opening full PHP blocks or calling facade methods inside your views.
- Ensure all pages include the `@alternateLinks` directive within the `<head>` of the HTML to preserve international SEO requirements.
