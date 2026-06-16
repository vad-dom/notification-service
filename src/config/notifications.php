<?php

return [
    'rate_limit' => [
        'batch_requests_per_minute' => env('NOTIFICATION_BATCH_RATE_LIMIT_PER_MINUTE', 10),
    ],
];
