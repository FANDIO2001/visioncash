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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->integer('frequency_day')->nullable();
            $table->integer('frequency_weekday')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_occurrence');
            $table->boolean('is_active')->default(true);
            $table->string('description');
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'yearly']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
