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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->unique(['user_id', 'channel_id']);
            $table->boolean('transaction_received')->default(true);
            $table->boolean('budget_80_percent')->default(true);
            $table->boolean('budget_100_percent')->default(true);
            $table->boolean('daily_summary')->default(true);
            $table->boolean('monthly_summary')->default(true);
            $table->boolean('suspicious_transaction')->default(true);
            $table->boolean('subscription_alerts')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
