<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;

beforeEach(function (): void {
    $this->config = new LocalizationConfig;
});

test('it can get primary drivers', function (): void {
    Config::set('octane-localization.drivers', ['Driver1', 'Driver2']);

    expect($this->config->getPrimaryDrivers())->toBe(['Driver1', 'Driver2']);
});

test('it throws exception if primary drivers are missing', function (): void {
    Config::set('octane-localization.drivers');

    expect(fn () => $this->config->getPrimaryDrivers())
        ->toThrow(InvalidConfiguration::class);
});

test('it throws exception if primary drivers is not an array', function (): void {
    Config::set('octane-localization.drivers', 'string');

    expect(fn () => $this->config->getPrimaryDrivers())
        ->toThrow(InvalidConfiguration::class);
});

test('it can get all extension drivers', function (): void {
    Config::set('octane-localization.ext', [
        'livewire' => ['drivers' => ['Driver1']],
        'other' => ['drivers' => ['Driver2', 'Driver1']],
    ]);

    $drivers = $this->config->getAllExtensionDrivers();

    expect($drivers)->toHaveCount(2)
        ->and($drivers)->toContain('Driver1', 'Driver2');
});

test('it can get extension drivers for a specific extension', function (): void {
    Config::set('octane-localization.ext.livewire.drivers', ['Driver1']);

    expect($this->config->getExtensionDrivers('livewire'))->toBe(['Driver1']);
    expect($this->config->getExtensionDrivers('missing'))->toBe([]);
});

test('it can get default locale', function (): void {
    Config::set('octane-localization.default_locale', 'fr');

    expect($this->config->getDefaultLocale())->toBe('fr');
});

test('it can get fallback locale', function (): void {
    Config::set('app.fallback_locale', 'es');

    expect($this->config->getDefaultFallbackLocale())->toBe('es');
});

test('it can get supported locales and normalize them', function (): void {
    // Simple list
    Config::set('octane-localization.supported_locales', ['en', 'es']);

    // Create a new instance to clear internal cache
    $config = new LocalizationConfig;

    expect($config->getSupportedLocales())->toBe([
        'en' => ['name' => 'en'],
        'es' => ['name' => 'es'],
    ]);

    // Detailed list
    Config::set('octane-localization.supported_locales', [
        'en' => ['name' => 'English'],
        'fr' => ['name' => 'French'],
    ]);

    $config = new LocalizationConfig;

    expect($config->getSupportedLocales())->toBe([
        'en' => ['name' => 'English'],
        'fr' => ['name' => 'French'],
    ]);
});

test('it can get supported locale codes', function (): void {
    Config::set('octane-localization.supported_locales', ['en', 'es']);

    $config = new LocalizationConfig;

    expect($config->getSupportedLocaleCodes())->toBe(['en', 'es']);
});

test('it can check if a locale is supported', function (): void {
    Config::set('octane-localization.supported_locales', ['en', 'es']);

    $config = new LocalizationConfig;

    expect($config->isSupportedLocale('en'))->toBeTrue();
    expect($config->isSupportedLocale('fr'))->toBeFalse();
    expect($config->isSupportedLocale(null))->toBeFalse();
    expect($config->isSupportedLocale(''))->toBeFalse();
});

test('it can get parameter key', function (): void {
    Config::set('octane-localization.parameter_key', 'lang');

    expect($this->config->getParameterKey())->toBe('lang');
});

test('it returns default cookie expiration', function (): void {
    // We don't set the config, so it should return the default
    expect($this->config->getCookieExpiration())->toBe(1440);
});

test('it returns explicit cookie expiration', function (): void {
    Config::set('octane-localization.cookie_ttl', 60);

    expect($this->config->getCookieExpiration())->toBe(60);
});

test('it can check if redirection is enabled', function (): void {
    config(['localization.redirections.active' => false]);
    expect($this->config->isRedirectionEnabled())->toBeFalse();

    config(['localization.redirections.active' => true]);
    expect($this->config->isRedirectionEnabled())->toBeTrue();
});

test('it can get redirection excluded paths', function (): void {
    config(['localization.redirections.except' => ['api/*']]);
    expect($this->config->getRedirectionExcludedPaths())->toBe(['api/*']);
});
