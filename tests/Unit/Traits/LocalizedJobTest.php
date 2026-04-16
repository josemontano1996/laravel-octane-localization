<?php

declare(strict_types=1);

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Traits\LocalizedJob;
use Mockery;

// Dummy class to use the trait
class LocalizedJobStub
{
    use LocalizedJob;
}

beforeEach(function () {
    $this->context = Mockery::mock(LocalizationContextInterface::class);
    $this->manager = Mockery::mock(LocalizationManagerInterface::class);

    app()->instance(LocalizationContextInterface::class, $this->context);
    app()->instance(LocalizationManagerInterface::class, $this->manager);

    $this->job = new LocalizedJobStub;
});

test('it restores localization when locale exists in context', function () {
    $this->context->shouldReceive('get')->andReturn('es');

    $this->manager->shouldReceive('setLocale')->once()->with('es');
    $this->manager->shouldReceive('syncWithApplication')->once();

    $this->job->restoreLocalization();
});

test('it does nothing when no locale exists in context', function () {
    $this->context->shouldReceive('get')->andReturn(null);

    $this->manager->shouldNotReceive('setLocale');
    $this->manager->shouldNotReceive('syncWithApplication');

    $this->job->restoreLocalization();
});

test('it resets localization', function () {
    $this->manager->shouldReceive('flush')->once();

    $this->job->resetLocalization();
});
