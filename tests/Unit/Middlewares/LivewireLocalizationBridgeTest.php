<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Enums\SupportedExtensions;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LivewireLocalizationBridge;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('bridges localization when a Livewire header is present', function () {
    // 1. Arrange
    // Use the real config from your TestCase setup
    $config = app(LocalizationConfigInterface::class);
    $livewireDrivers = $config->getExtensionDrivers(SupportedExtensions::LIVEWIRE->value);

    $request = Request::create('/', 'POST');
    $request->headers->set('X-Livewire', 'true');

    // Mock the Manager to verify interaction
    $manager = Mockery::mock(LocalizationManagerInterface::class);

    // The core expectation: Manager must discover using Livewire-specific drivers
    $manager->shouldReceive('discover')
        ->once()
        ->with($request, $livewireDrivers);

    $manager->shouldReceive('syncWithApplication')
        ->once();

    $middleware = new LivewireLocalizationBridge($manager, $config);

    // 2. Act
    $response = $middleware->handle($request, function ($req) {
        return new Response('Livewire Handled');
    });

    // 3. Assert
    expect($response->getContent())->toBe('Livewire Handled');
});

it('bypasses logic when the Livewire header is missing', function () {
    // 1. Arrange
    $request = Request::create('/', 'GET');
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $config = app(LocalizationConfigInterface::class);

    // Manager should not be touched if it's not a Livewire request
    $manager->shouldNotReceive('discover');
    $manager->shouldNotReceive('syncWithApplication');

    $middleware = new LivewireLocalizationBridge($manager, $config);

    // 2. Act
    $response = $middleware->handle($request, function ($req) {
        return new Response('Normal Request');
    });

    // 3. Assert
    expect($response->getContent())->toBe('Normal Request');
});

it('cleans up state on terminate for Livewire requests', function () {
    // 1. Arrange
    $request = Request::create('/', 'POST');
    $request->headers->set('X-Livewire', 'true');

    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $manager->shouldReceive('flush')->once();

    $middleware = new LivewireLocalizationBridge($manager, app(LocalizationConfigInterface::class));

    // 2. Act
    $middleware->terminate($request, new Response);

    // Mockery verifies 'flush' was called once
});

it('flushes manager state even if the Livewire request crashes', function () {
    // 1. Arrange
    $request = Request::create('/', 'POST');
    $request->headers->set('X-Livewire', 'true');

    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $config = app(LocalizationConfigInterface::class);

    // We expect the standard flow...
    $manager->shouldReceive('discover')->once();
    $manager->shouldReceive('syncWithApplication')->once();

    // ...and crucially, the cleanup
    $manager->shouldReceive('flush')->once();

    $middleware = new LivewireLocalizationBridge($manager, $config);

    // 2. Act
    try {
        $middleware->handle($request, function () {
            throw new Exception('Livewire Component Explosion');
        });
    } catch (Exception $e) {
        // Carry on
    } finally {
        // 3. Simulate the Kernel's termination phase
        $middleware->terminate($request, new Response);
    }

    // Mockery asserts flush() was called
});
