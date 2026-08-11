<?php

use App\Enums\NotificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_batch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('recipient_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status');
            $table->unsignedTinyInteger('priority');

            $table->string('provider_message_id')->unique()->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index(['recipient_id', 'created_at']);
        });

        DB::statement(sprintf(
            "CREATE INDEX notifications_stuck_queued_at_index ON notifications (queued_at) WHERE status = '%s'",
            NotificationStatus::Queued->value,
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
