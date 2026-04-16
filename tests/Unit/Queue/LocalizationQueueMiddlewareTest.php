<?php

use Josemontano1996\LaravelOctaneLocalization\Queue\LocalizationQueueMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Contracts\Support\LocalizationAwareJob;

it('restores and resets localization for aware jobs', function () {
    // 1. Arrange
    $middleware = new LocalizationQueueMiddleware();
    
    // Create a mock job that implements the required interface
    $job = Mockery::mock(LocalizationAwareJob::class);
    
    // Expectations
    $job->shouldReceive('restoreLocalization')->once()->ordered();
    $job->shouldReceive('resetLocalization')->once()->ordered();

    $nextCalled = false;
    $next = function ($job) use (&$nextCalled) {
        $nextCalled = true;
    };

    // 2. Act
    $middleware->handle($job, $next);

    // 3. Assert
    expect($nextCalled)->toBeTrue();
});

it('resets localization even if the job fails', function () {
    // 1. Arrange
    $middleware = new LocalizationQueueMiddleware();
    $job = Mockery::mock(LocalizationAwareJob::class);

    $job->shouldReceive('restoreLocalization')->once();
    // This MUST be called even on failure
    $job->shouldReceive('resetLocalization')->once();

    $next = function ($job) {
        throw new Exception('Job failed');
    };

    // 2. Act & Assert
    try {
        $middleware->handle($job, $next);
    } catch (Exception $e) {
        expect($e->getMessage())->toBe('Job failed');
    }
    
    // Mockery will verify resetLocalization was called in the finally block
});

it('does nothing for jobs that are not localization aware', function () {
    // 1. Arrange
    $middleware = new LocalizationQueueMiddleware();
    
    // A standard anonymous class (not implementing the interface)
    $job = new class {};
    
    $nextCalled = false;
    $next = function ($job) use (&$nextCalled) {
        $nextCalled = true;
    };

    // 2. Act
    $middleware->handle($job, $next);

    // 3. Assert
    expect($nextCalled)->toBeTrue();
    // No exceptions thrown, no interface methods called
});

it('calls resetLocalization even if the job throws an exception', function () {
    // 1. Arrange
    $middleware = new LocalizationQueueMiddleware();
    $job = Mockery::mock(LocalizationAwareJob::class);

    // Expectations: Both should be called exactly once
    $job->shouldReceive('restoreLocalization')->once();
    $job->shouldReceive('resetLocalization')->once();

    // A closure that simulates a failing job
    $next = function ($job) {
        throw new \RuntimeException('Database connection failed');
    };

    // 2. Act
    $exceptionThrown = false;
    try {
        $middleware->handle($job, $next);
    } catch (\RuntimeException $e) {
        $exceptionThrown = true;
        expect($e->getMessage())->toBe('Database connection failed');
    }

    // 3. Assert
    expect($exceptionThrown)->toBeTrue();
    // Mockery automatically verifies that resetLocalization() was called 
    // because it was marked as ->once()
});