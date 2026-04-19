<?php

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LivewireLocalizationBridge;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationManager;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;
use Josemontano1996\LaravelOctaneLocalization\Providers\LocalizationServiceProvider;
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

it('registers manager, redirector, context, and seo helper as scoped services', function () {
    $scopedInterfaces = [
        LocalizationManagerInterface::class,
        LocalizationRedirectorInterface::class,
        LocalizationContextInterface::class,
        SeoHelperInterface::class,
    ];

    foreach ($scopedInterfaces as $interface) {
        $instance1 = app($interface);

        // Within the same scope, the same instance is returned
        expect($instance1)->toBe(app($interface), "Expected {$interface} to return the same instance within scope");

        // Simulate an Octane request boundary by clearing the scoped instance
        app()->forgetInstance($interface);

        // After reset, a fresh instance must be returned
        $instance2 = app($interface);

        expect($instance1)
            ->not->toBe($instance2, "Expected {$interface} to return a new instance after scope reset");
    }
});

it('registers config and url parser as true singletons', function () {
    $singletonInterfaces = [
        LocalizationConfigInterface::class,
        URLParserInterface::class,
    ];

    foreach ($singletonInterfaces as $interface) {
        $instance1 = app($interface);
        $instance2 = app($interface);

        // Singletons always return the exact same instance
        expect($instance1)->toBe($instance2, "Expected {$interface} to be a singleton");
    }
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

it('calls reset on the locale manager during boot', function () {
    // Swap the real manager for a mock so we can assert reset() is called
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $manager->shouldReceive('reset')->once();

    app()->instance(LocalizationManagerInterface::class, $manager);

    // Boot a fresh provider instance — this simulates an Octane worker restart
    $provider = new LocalizationServiceProvider(app());
    $provider->boot();
});
