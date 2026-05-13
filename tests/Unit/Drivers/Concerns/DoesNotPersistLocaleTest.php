<?php

use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;
use Symfony\Component\HttpFoundation\Request;

it('does absolutely nothing to the request or the environment', function (): void {
    $tester = new class
    {
        use DoesNotPersistLocale;
    };
    $request = Request::create('/test');

    // 1. Snapshot the state
    $requestBefore = serialize(clone $request);
    $localeBefore = app()->getLocale();

    // 2. Act
    $tester->storeLocale('fr', $request);

    // 3. Assert total lack of change
    expect(serialize($request))->toBe($requestBefore)
        ->and(app()->getLocale())->toBe($localeBefore);
});
