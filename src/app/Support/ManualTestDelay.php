<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Applies optional delay from config notifications.manual_test_delay_seconds.
 */
final class ManualTestDelay
{
    public static function apply(string $step): void
    {
        $seconds = (int) config('notifications.manual_test_delay_seconds', 0);

        if ($seconds <= 0) {
            return;
        }

        Log::info('Manual test delay: waiting before processing step', [
            'step' => $step,
            'seconds' => $seconds,
        ]);

        sleep($seconds);
    }
}
