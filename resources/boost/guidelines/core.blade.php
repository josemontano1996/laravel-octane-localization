# System Architecture & Domain Logic

This package is built to prevent language state leakage in long-lived Laravel Octane workers but is also compatible with regular FPM. AI agents and developers must use the documented lifecycle and architecture exactly: detect the locale, sync it into the request-scoped environment, and reset the global state before the next request.

## 1. The Localization Lifecycle (Request Flow)
When a request enters the application, the `LocalizationManager` coordinates the lifecycle:
1. **Detect (`detect(Request)`):** Iterates through configured drivers to resolve the locale.
2. **Sync (`syncWithApplication()`):** Applies the locale to the current worker request by updating `App::setLocale`, `URL::defaults`, `Carbon`, and optionally `Number`.
3. **Flush (`reset()`):** This is non-negotiable for Octane. After each request or queued job, the manager must clear localized state and restore the default locale so the next execution starts clean.

> Important: In Octane, you must register `ResetLocalizationStateListener` in `config/octane.php` listeners so the reset logic runs for each worker request.

## 2. Container Strategy
The package uses container bindings to isolate state safely:
- **Scoped** bindings are used for stateful request data such as `LocalizationState`, `LocalizationMiddleware`, and driver instances that may hold per-request state.
- **Singleton** bindings are used for stateless services like `LocalizationConfig`, `URLParser`, `SeoHelper`, and `LocalizationManager`, since they do not retain locale data between calls.

## 3. The Livewire Bridge
Livewire requests do not always include a localized route segment, so the package provides a special fallback path:
- The `LivewireLocalizationBridge` rewrites Livewire requests to use the `ext.livewire.drivers` driver stack.
- This typically uses `RefererDriver` to infer the locale from the page that mounted the Livewire component.
- Do not assume Livewire routes are already localized by URL; the fallback driver stack is critical.

## 4. Driver Architecture
- Drivers implement `LocaleDriverInterface`, providing `getLocale()` and `storeLocale()`.
- Read-only drivers should use `DoesNotPersistLocale` to satisfy the interface without persisting state.
- Persistent drivers (`SessionDriver`, `CookieDriver`, etc.) should use the same `parameter_key` to save and retrieve the locale consistently.

## 5. Queue Job Architecture
Queued jobs are executed in the same worker process as previous jobs, so they require explicit locale restoration and cleanup:
- The locale is transported into the queued payload via Laravel 11 `Context`.
- `LocalizationQueueMiddleware` restores the locale before `handle()` runs.
- It always calls `resetLocalization()` in a `finally` block so worker state is returned to the default locale after job execution.

## AI / Boost Model Guidance
- Always treat this package as Octane-safe: do not recommend simple global locale storage without reset.
- Prefer the package's route macros, middleware, job middleware, and configuration values.
- If asked about Livewire localization, reference `LivewireLocalizationBridge` and `ext.livewire.drivers`.
- If asked about job localization, reference `LocalizationAwareJob`, `LocalizedJob`, `LocalizationQueueMiddleware`, and the restore/reset pattern.
