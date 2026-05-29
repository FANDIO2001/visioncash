<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('notification_preferences', ['user_id', 'channel_id'])) {
            return;
        }

        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['channel_id']);
        });

        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('channel_id')->references('id')->on('notification_channels')->cascadeOnDelete();
            $table->unique(['user_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasIndex('notification_preferences', ['user_id', 'channel_id'])) {
            return;
        }

        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['channel_id']);
            $table->dropUnique(['user_id', 'channel_id']);
        });

        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->unique('user_id');
            $table->unique('channel_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('channel_id')->references('id')->on('notification_channels')->cascadeOnDelete();
        });
    }
};
