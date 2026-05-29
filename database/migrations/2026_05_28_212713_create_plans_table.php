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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->decimal('price_monthly_xaf', 10, 2);
            $table->decimal('price_annual_xaf', 10, 2);
            $table->integer('max_accounts')->nullable();
            $table->integer('max_transactions_month')->nullable();
            $table->integer('max_categories')->nullable();
            $table->integer('max_budgets')->nullable();
            $table->integer('max_integrations')->nullable();
            $table->boolean('export_pdf')->default(false);
            $table->boolean('export_excel')->default(false);
            $table->boolean('csv_import')->default(false);
            $table->boolean('recurring_transactions')->default(false);
            $table->boolean('forecast')->default(false);
            $table->integer('history_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
