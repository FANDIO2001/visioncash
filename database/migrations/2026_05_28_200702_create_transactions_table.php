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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('transaction_type', ['income', 'expense']);
            $table->string('description')->nullable();
            $table->string('currency', 3)->default('XAF');
            $table->boolean('is_manual')->default(true);
            $table->boolean('is_read_only')->default(false);
            $table->string('external_reference', 100)->nullable();
            $table->string('attachment_url', 255)->nullable();
            $table->enum('created_by_source', ['system','manual'])->default('manual');
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'transaction_date']);
            $table->index(['account_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
