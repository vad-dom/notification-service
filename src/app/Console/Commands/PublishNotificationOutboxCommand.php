<?php

namespace App\Console\Commands;

use App\Services\NotificationOutboxService;
use Illuminate\Console\Command;

class PublishNotificationOutboxCommand extends Command
{
    protected $signature = 'notifications:publish-outbox {--limit=100 : Maximum outbox entries to publish}';

    protected $description = 'Publish pending notification outbox entries to the queue';

    public function handle(NotificationOutboxService $outboxService): int
    {
        $limit = (int) ($this->option('limit') ?: config('notifications.outbox.publish_limit'));

        $publishedCount = $outboxService->publishPending($limit);

        $this->info("Published {$publishedCount} outbox entr".($publishedCount === 1 ? 'y' : 'ies').'.');

        return self::SUCCESS;
    }
}
