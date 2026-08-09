<?php

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
        Schema::create('notification_outbox', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('queue_name');
            $table->unsignedTinyInteger('priority');
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });

        DB::statement(
            'CREATE INDEX notification_outbox_pending_priority_id_index ON notification_outbox (priority DESC, id) WHERE published_at IS NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_outbox');
    }
};
