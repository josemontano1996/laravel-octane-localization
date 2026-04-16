<?php

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LivewireLocalizationBridge;
use Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationManager;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;
use Josemontano1996\LaravelOctaneLocalization\Services\SeoHelper;

it('binds interfaces to implementations', function () {
    expect(app(LocalizationConfigInterface::class))->toBeInstanceOf(LocalizationConfig::class);
    expect(app(LocalizationManagerInterface::class))->toBeInstanceOf(LocalizationManager::class);
    expect(app(SeoHelperInterface::class))->toBeInstanceOf(SeoHelper::class);
});

it('registers localization state as a scoped service', function () {
    // 1. Get the first instance
    $instance1 = app(LocalizationStateInterface::class);

    // 2. Verify that within the same "scope", it's a singleton
    expect($instance1)->toBe(app(LocalizationStateInterface::class));

    // 3. Manually clear the instance from the container
    // This simulates the end of a request cycle in Octane
    app()->forgetInstance(LocalizationStateInterface::class);

    // 4. Resolve again - it should be a brand new instance
    $instance2 = app(LocalizationStateInterface::class);

    expect($instance1)->not->toBe($instance2)
        ->and($instance2)->toBeInstanceOf(LocalizationState::class);
});

it('registers the config as a singleton', function () {
    $instance1 = app(LocalizationConfigInterface::class);

    // Even after clearing instances, singletons usually persist in tests
    // depending on how you refresh the app, but we can check the binding type:
    expect(app()->isShared(LocalizationConfigInterface::class))->toBeTrue();
});

it('prepends the livewire bridge middleware to the web group', function () {
    $router = app('router');
    $middlewareGroups = $router->getMiddlewareGroups();

    // The 'web' group was defined in TestCase, and the Provider prepended to it.
    expect($middlewareGroups)->toHaveKey('web')
        ->and($middlewareGroups['web'])->toContain(LivewireLocalizationBridge::class);

    // Verify it is at the very beginning (index 0)
    expect($middlewareGroups['web'][0])->toBe(LivewireLocalizationBridge::class);
});

it('merges the package configuration', function () {
    $config = config('octane-localization');

    expect($config)->toBeArray()
        ->and($config['parameter_key'])->toBe('locale');
});
