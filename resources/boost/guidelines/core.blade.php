# System Architecture & Domain Logic

When working on this codebase, you must understand how this package manages localization state specifically for **Laravel Octane**. Failure to understand the lifecycle will result in memory leaks or broken features.

## 1. The Localization Lifecycle (Request Flow)
When a request enters the application, the `LocalizationManager` coordinates the lifecycle:
1. **Detect (`detect(Request)`):** Iterates through drivers defined in config to find a locale.
2. **Sync (`syncWithApplication()`):** Modifies the global facade states: `App::setLocale`, `URL::defaults`, `Carbon`, etc. This state is strictly bound to the current worker request.
3. **Flush (`reset()`):** The most critical step. Once the response is sent, the Manager absolutely must wipe the global application facades clean and clear the localized scoped bounds so the next user's Octane request receives a fresh, unaltered default state.

## 2. Container Strategy
To prevent Octane leakages:
- The **Data/State** objects (e.g., `LocalizationState` holding the active locale string, `LocalizationMiddleware`) are bound as **Scoped** (`$app->scoped()`). This means a new instance is created for every single HTTP request.
- The **Actions/Services** (e.g., `LocalizationConfig`, `URLParser`, `SeoHelper`, `LocalizationManager`) are bound as **Singletons** (`$app->singleton()`) because they hold zero request-specific data. They only read from config or accept parameters.

## 3. The Livewire Bridge
Livewire breaks traditional URL drivers because it hits a generic, non-localized endpoint (`/livewire/update`).
- We do not require livewire as a composer dependency.
- The `LivewireLocalizationBridge` intercepts these requests and dynamically swaps the `drivers` array out for the fallback `ext.livewire.drivers` stack defined in the config. 
- Usually, this stack utilizes the `RefererDriver` to pull the locale from the `Referer` header of the page the Livewire component is mounted on.

## 4. Driver Architecture
- Drivers (`LocaleDriverInterface`) are responsible for reading `getLocale()` and persisting `storeLocale()`.
- Some drivers only read data (like URL segments or API Headers) and have no business "saving" data back to sessions or databases. For these read-only mechanisms, they use the `DoesNotPersistLocale` trait, which fulfills the persistence contract with a blank No-Op.

## 5. Queue Job Architecture
When a localized job is pushed to the queue, the worker executing the job has no idea what the language was.
- We use the Laravel 11 Context feature to transport the locale string seamlessly inside the queued payload.
- The `LocalizationQueueMiddleware` intercepts the job *execution* on the worker.
- It operates a **Restore & Reset** pattern: It injects the context locale into the system (`restoreLocalization`), runs the user's code, and then immediately runs `resetLocalization` using a `try/finally` block to ensure the next job in the queue's worker gets a clean slate.
