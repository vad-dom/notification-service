<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

            $table->string('provider_message_id')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['recipient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
