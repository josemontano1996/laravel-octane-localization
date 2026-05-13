<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Registrars;

use Illuminate\Support\Facades\Route;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddleware;

class RegisterMacros
{
    public static function register(): void
    {
        Route::macro('localizedWithPrefix', function ($callback = null) {
            $config = app(LocalizationConfigInterface::class);
            $key = $config->getParameterKey();
            $supportedLocales = $config->getSupportedLocaleCodes();

            return Route::prefix("{{$key}}")->whereIn($key, $supportedLocales)
                ->middleware(LocalizationMiddleware::class)
                ->group($callback);
        });
    }
}
