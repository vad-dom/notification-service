<?php

namespace App\Console\Commands;

use App\Services\NotificationReconciliationService;
use Illuminate\Console\Command;

class ReconcileStuckNotificationsCommand extends Command
{
    protected $signature = 'notifications:reconcile-stuck {--limit=100 : Maximum stuck notifications to republish}';

    protected $description = 'Republish send jobs for notifications stuck in queued status';

    public function handle(NotificationReconciliationService $reconciliationService): int
    {
        $limit = (int) ($this->option('limit') ?: config('notifications.reconciliation.publish_limit'));

        $republishedCount = $reconciliationService->republishStuckQueued($limit);

        $this->info("Republished {$republishedCount} stuck notification".($republishedCount === 1 ? '' : 's').'.');

        return self::SUCCESS;
    }
}
