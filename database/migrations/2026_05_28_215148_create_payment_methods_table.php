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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->enum('payment_type', ['credit_card', 'mobile_money'])->comment("Type : 'credit_card', 'mobile_money'");
            $table->string('last_four', 4)->comment('4 derniers chiffres');
            $table->string('payment_provider', 50)->comment("Provider : 'stripe', 'cinetpay'");
            $table->string('external_payment_id', 100)->comment('ID externe du moyen de paiement');
            $table->boolean('is_default')->default(false)->comment('Moyen de paiement par défaut');
            $table->boolean('is_active')->default(true)->comment('Moyen actif ou supprimé');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
