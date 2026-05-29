<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('notifications', ['user_id', 'is_read'])) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasIndex('notifications', ['user_id', 'is_read'])) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read']);
        });
    }
};
