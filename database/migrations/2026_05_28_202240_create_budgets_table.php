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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            // $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->decimal('spent', 15, 2)->default(0);
            $table->enum('period_type', ['monthly', 'weekly', 'yearly'])->default('monthly'); // e.g., 'monthly', 'weekly', 'yearly'
            $table->date('start_date');
            $table->integer('alert_threshold_percentage')->default(80); // Alert when spent reaches this percentage of the budget
            $table->boolean('alert_sent_80')->default(false); 
            $table->boolean('alert_sent_100')->default(false); 
            $table->boolean('is_active')->default(true); 
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
