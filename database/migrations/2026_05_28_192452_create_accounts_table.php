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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('integration_id')->nullable();
            $table->string('account_number');
            $table->unique(['user_id', 'account_number']);
            $table->string('account_name');
            $table->boolean('is_active')->default(false)->index();
            $table->string('currency', 3);
            $table->string('color', 7)->nullable();
            $table->string('iban', 34)->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
