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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('external_account_id');
            $table->string('external_account_name')->nullable();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('next_sync_at')->nullable();
            $table->enum('sync_status', ['idle', 'syncing', 'error'])->default('idle');
            $table->string('last_error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('integration_id')
                ->references('id')
                ->on('integrations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['integration_id']);
        });

        Schema::dropIfExists('integrations');
    }
};
