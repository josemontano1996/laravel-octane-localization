<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Registrars;

use Illuminate\Support\Facades\Blade;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;

class RegisterBladeDirectives
{
    /**
     * Register development-friendly localization blade directives.
     */
    public static function register(): void
    {
        // 1. @currentLocale
        // Outputs the current application locale string (e.g. 'en')
        Blade::directive('currentLocale', function () {
            return '<?php echo app()->getLocale(); ?>';
        });

        // 2. @isLocale('en')
        // Supports @elseisLocale('es'), @else and @endisLocale automatically.
        Blade::if('isLocale', function (string $locale) {
            return app()->getLocale() === $locale;
        });

        // 4. @supportedLocales($code, $data) ... @endsupportedLocales
        // Loops through all locales defined in the config.
        Blade::directive('supportedLocales', function (string $expression) {
            if (empty($expression)) {
                $expression = '$code, $data';
            }

            $parts = array_map('trim', explode(',', $expression));
            $key = $parts[0] ?? '$code';
            $value = $parts[1] ?? '$data';

            return "<?php foreach(app(" . LocalizationConfigInterface::class . "::class)->getSupportedLocales() as {$key} => {$value}): ?>";
        });

        Blade::directive('endsupportedLocales', function () {
            return '<?php endforeach; ?>';
        });

        // 5. @localizedUrl('en')
        // Generates a URL for the current request but pointing to another locale.
        Blade::directive('localizedUrl', function (string $locale) {
            return "<?php echo app(" . URLParserInterface::class . "::class)->getLocalizedUrl(url()->current(), {$locale}); ?>";
        });

        // 6. @alternateLinks
        // Generates <link rel="alternate" ... /> tags for SEO in the <head>
        Blade::directive('alternateLinks', function () {
            return "<?php echo app(" . SeoHelperInterface::class . "::class)->getAlternateLinks(); ?>";
        });
    }
}
