<?php

namespace App\DTO;

use App\Models\NotificationBatch;

readonly class NotificationBatchCreationResult
{
    public function __construct(
        public NotificationBatch $batch,
        public bool $created,
    ) {}
}
