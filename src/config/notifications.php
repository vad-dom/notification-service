<?php

return [
    'rate_limit' => [
        'batch_requests_per_minute' => env('NOTIFICATION_BATCH_RATE_LIMIT_PER_MINUTE', 10),
        'batch_decay_seconds' => env('NOTIFICATION_BATCH_RATE_LIMIT_DECAY_SECONDS', 60),
    ],

    'outbox' => [
        'relay_queue' => env('NOTIFICATION_OUTBOX_RELAY_QUEUE', 'notifications.outbox'),
        'publish_limit' => env('NOTIFICATION_OUTBOX_PUBLISH_LIMIT', 100),
    ],

    'reconciliation' => [
        'stuck_queued_threshold_minutes' => env('NOTIFICATION_STUCK_QUEUED_THRESHOLD_MINUTES', 5),
        'publish_limit' => env('NOTIFICATION_RECONCILIATION_PUBLISH_LIMIT', 100),
    ],

    // Set to 15-30 for manual outbox testing; keep 0 in production.
    'manual_test_delay_seconds' => env('NOTIFICATION_MANUAL_TEST_DELAY_SECONDS', 0),
];
