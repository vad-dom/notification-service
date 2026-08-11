<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class PublishNotificationOutboxScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_recovery_command_is_scheduled(): void
    {
        $event = collect(Schedule::events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'notifications:publish-outbox'));

        $this->assertNotNull($event);
        $this->assertSame('* * * * *', $event->expression);
    }

    public function test_stuck_notification_reconciliation_command_is_scheduled(): void
    {
        $event = collect(Schedule::events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'notifications:reconcile-stuck'));

        $this->assertNotNull($event);
        $this->assertSame('* * * * *', $event->expression);
    }
}
