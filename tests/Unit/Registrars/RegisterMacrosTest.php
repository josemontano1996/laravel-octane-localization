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
    
    // Setup a fresh mock for every test to avoid expectation pollution
    $this->config = Mockery::mock(LocalizationConfigInterface::class);
    $this->app->instance(LocalizationConfigInterface::class, $this->config);
});

test('it registers localizedWithPrefix macro', function () {
    expect(Route::hasMacro('localizedWithPrefix'))->toBeTrue();
});

test('localizedWithPrefix macro creates a prefixed group with middleware', function () {
    $this->config->shouldReceive('getParameterKey')->andReturn('lang');
    $this->config->shouldReceive('getSupportedLocaleCodes')->andReturn(['en', 'es']);

    Route::localizedWithPrefix(function () {
        Route::get('/test-basic', fn () => 'test')->name('test.basic');
    });

    $route = collect(Route::getRoutes())->first(fn ($r) => $r->getName() === 'test.basic');

    expect($route)->not->toBeNull()
        ->and($route->getPrefix())->toBe('{lang}') // Assert exact match
        ->and($route->gatherMiddleware())->toContain(LocalizationMiddleware::class);
});

test('localizedWithPrefix macro applies whereIn constraint to the prefix', function () {
    $this->config->shouldReceive('getParameterKey')->andReturn('locale');
    $this->config->shouldReceive('getSupportedLocaleCodes')->andReturn(['en', 'es', 'fr']);

    Route::localizedWithPrefix(function () {
        Route::get('/test-constraint', fn () => 'test')->name('test.constraint');
    });

    $route = collect(Route::getRoutes())->first(fn ($r) => $r->getName() === 'test.constraint');

    expect($route)->not->toBeNull();
    
    // In Laravel, the whereIn constraint is compiled into a regex pattern
    // Access the 'wheres' property directly on the Route object
    $wheres = $route->wheres; 

    expect($wheres)->toHaveKey('locale')
        ->and($wheres['locale'])->toBe('en|es|fr');
});