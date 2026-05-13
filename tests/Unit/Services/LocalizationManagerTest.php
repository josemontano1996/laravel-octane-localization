<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationContext;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationManager;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;

beforeEach(function (): void {
    $this->config = new LocalizationConfig;
    $this->state = new LocalizationState;
    $this->context = new LocalizationContext;
    $this->manager = new LocalizationManager($this->config, $this->state, $this->context);

    Config::set('octane-localization.supported_locales', ['en', 'es', 'fr']);
    Config::set('octane-localization.default_locale', 'en');
    Config::set('octane-localization.parameter_key', 'locale');
});

test('it can set locale manually', function (): void {
    $this->manager->setLocale('es');
    expect($this->state->get())->toBe('es');

    $this->manager->setLocale('unsupported');
    expect($this->state->get())->toBe('en'); // Falls back to default
});

test('it can detect locale using drivers', function (): void {
    $driver = Mockery::mock(LocaleDriverInterface::class);
    $driver->shouldReceive('getLocale')->andReturn('es');
    $driver->shouldReceive('storeLocale')->once()->with('es', Mockery::any());

    // Create a real class to satisfy class_exists
    if (!class_exists('TestDriver')) {
        eval ('class TestDriver {}');
    }

    $this->app->instance('TestDriver', $driver);
    Config::set('octane-localization.drivers', ['TestDriver']);

    $request = Request::create('/es');
    $this->manager->detect($request);

    expect($this->state->get())->toBe('es');
});

test('it can discover locale using custom driver stack', function (): void {
    $driver = Mockery::mock(LocaleDriverInterface::class);
    $driver->shouldReceive('getLocale')->andReturn('fr');

    if (!class_exists('CustomDriver')) {
        eval ('class CustomDriver {}');
    }

    $this->app->instance('CustomDriver', $driver);

    $request = Request::create('/fr');
    $this->manager->discover($request, ['CustomDriver']);

    expect($this->state->get())->toBe('fr');
});

test('it syncs with application state', function (): void {
    $this->state->set('es');

    // Use current time to test Carbon
    $now = Carbon::now();

    $this->manager->syncWithApplication();

    expect(App::getLocale())->toBe('es');
    expect($this->context->get())->toBe('es');

    // Check URL defaults
    $defaults = URL::getDefaultParameters();
    expect($defaults['locale'])->toBe('es');

    // Check Carbon
    expect(Carbon::getLocale())->toBe('es');
});

test('it can flush application state', function (): void {
    $config = app(LocalizationConfigInterface::class);
    $original = $config->getDefaultLocale();
    $mutated = $config->getDefaultFallbackLocale();
    $this->state->set($mutated);

    $this->manager->syncWithApplication();

    expect(App::getLocale())->toBe($mutated);

    $this->manager->reset();

    expect(App::getLocale())->toBe($original);

    $defaults = URL::getDefaultParameters()[$config->getParameterKey()];
    expect($defaults)->toBe($original);

    expect(Carbon::getLocale())->toBe($original);
});
