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
        Schema::table('notification_outbox', function (Blueprint $table) {
            $table->unsignedTinyInteger('priority')->after('queue_name');
            $table->dropIndex(['published_at', 'id']);
            $table->index(['published_at', 'priority', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_outbox', function (Blueprint $table) {
            $table->dropIndex(['published_at', 'priority', 'id']);
            $table->index(['published_at', 'id']);
            $table->dropColumn('priority');
        });
    }
};
