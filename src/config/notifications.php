<?php

return [
    'rate_limit' => [
        'batch_requests_per_minute' => env('NOTIFICATION_BATCH_RATE_LIMIT_PER_MINUTE', 10),
    ],

    'outbox' => [
        'relay_queue' => env('NOTIFICATION_OUTBOX_RELAY_QUEUE', 'notifications.outbox'),
        'publish_limit' => env('NOTIFICATION_OUTBOX_PUBLISH_LIMIT', 100),
    ],
];
