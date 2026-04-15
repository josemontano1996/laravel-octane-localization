<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Queue;

use Josemontano1996\LaravelOctaneLocalization\Contracts\Support\LocalizationAwareJob;

class LocalizationQueueMiddleware
{
    /**
     * Handle the queued job.
     */
    public function handle(mixed $job, callable $next): void
    {
        if ($job instanceof LocalizationAwareJob) {
            $job->restoreLocalization();
        }

        try {
            $next($job);
        } finally {
            if ($job instanceof LocalizationAwareJob) {
                $job->resetLocalization();
            }
        }
    }
}
