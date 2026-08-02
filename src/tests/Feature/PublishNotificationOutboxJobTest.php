<?php

namespace Tests\Feature;

use App\Jobs\PublishNotificationOutboxJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PublishNotificationOutboxJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_to_outbox_relay_queue(): void
    {
        Queue::fake();

        PublishNotificationOutboxJob::dispatch(42);

        Queue::assertPushedOn(
            config('notifications.outbox.relay_queue'),
            PublishNotificationOutboxJob::class,
            fn (PublishNotificationOutboxJob $job): bool => $job->batchId === 42,
        );
    }
}
