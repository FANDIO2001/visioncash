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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['sent', 'failed', 'bounced']);
            $table->text('error_message')->nullable();
            $table->string('external_id', 255)->nullable();
            $table->timestamp('sent_at')->nullable();

            // $table->foreign('notification_id')->references('id')->on('notifications');
            // $table->foreign('channel_id')->references('id')->on('notification_channels');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
