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
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['pdf', 'excel', 'csv']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->date('period_start');
            $table->date('period_end');
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
