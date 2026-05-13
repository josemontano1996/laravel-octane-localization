<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Enums\SupportedExtensions;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LivewireLocalizationBridge;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('bridges localization when a Livewire header is present', function (): void {
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
    $response = $middleware->handle($request, function ($req): Response {
        return new Response('Livewire Handled');
    });

    // 3. Assert
    expect($response->getContent())->toBe('Livewire Handled');
});

it('bypasses logic when the Livewire header is missing', function (): void {
    // 1. Arrange
    $request = Request::create('/', 'GET');
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $config = app(LocalizationConfigInterface::class);

    // Manager should not be touched if it's not a Livewire request
    $manager->shouldNotReceive('discover');
    $manager->shouldNotReceive('syncWithApplication');

    $middleware = new LivewireLocalizationBridge($manager, $config);

    // 2. Act
    $response = $middleware->handle($request, function ($req): Response {
        return new Response('Normal Request');
    });

    // 3. Assert
    expect($response->getContent())->toBe('Normal Request');
});
