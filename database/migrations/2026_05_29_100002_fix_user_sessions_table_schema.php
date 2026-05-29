<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrates legacy user_sessions columns to hashed tokens.
     * No-op when create_session_connectios_table already uses the new schema.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('user_sessions', 'access_token')) {
            return;
        }

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropColumn(['access_token', 'refresh_token', 'expires_at', 'user_agent']);
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->string('access_token_hash', 64)->nullable()->after('user_id');
            $table->string('refresh_token_hash', 64)->nullable()->after('access_token_hash');
            $table->timestamp('expires_at')->nullable()->after('refresh_token_hash');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->index(['user_id', 'revoked']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('user_sessions', 'access_token_hash')) {
            return;
        }

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'revoked']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['access_token_hash', 'refresh_token_hash', 'expires_at', 'user_agent']);
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->string('access_token', 45)->nullable()->after('user_id');
            $table->string('refresh_token', 45)->nullable()->after('access_token');
            $table->string('expires_at', 45)->nullable()->after('refresh_token');
            $table->string('user_agent', 45)->nullable()->after('ip_address');
        });
    }
};
