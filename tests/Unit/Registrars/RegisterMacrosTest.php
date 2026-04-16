<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Tests\Unit\Registrars;

use Illuminate\Support\Facades\Route;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Registrars\RegisterMacros;
use Mockery;

beforeEach(function () {
    RegisterMacros::register();
});

test('it registers localizedWithPrefix macro', function () {
    expect(Route::hasMacro('localizedWithPrefix'))->toBeTrue();
});

test('localizedWithPrefix macro creates a prefixed group with middleware', function () {
    $config = Mockery::mock(LocalizationConfigInterface::class);
    $config->shouldReceive('getParameterKey')->andReturn('lang');
    $this->app->instance(LocalizationConfigInterface::class, $config);

    Route::localizedWithPrefix(function () {
        Route::get('/test-macro', fn () => 'test')->name('test.macro');
    });

    // Fresh look at the routes
    $route = collect(Route::getRoutes())->first(fn ($r) => $r->getName() === 'test.macro');

    expect($route)->not->toBeNull()
        ->and($route->getPrefix())->toContain('{lang}')
        ->and($route->gatherMiddleware())->toContain(LocalizationMiddleware::class);
});
