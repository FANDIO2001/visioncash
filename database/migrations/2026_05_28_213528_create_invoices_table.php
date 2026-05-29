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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->nullable(false)->comment('Montant facturé');
            $table->char('currency', 3)->default('XAF')->comment('Devise (ISO 4217)');
            $table->enum('status', ['paid', 'pending', 'failed', 'refunded'])->nullable(false)->index()->comment('État de la facture');
            $table->date('invoice_date')->nullable(false)->comment('Date de facturation');
            $table->timestamp('paid_at')->nullable()->comment('Date de paiement effectif');
            $table->text('pdf_url')->nullable()->comment('URL du PDF de facture généré');
            $table->string('stripe_invoice_id', 100)->nullable()->unique()->comment('Référence Stripe');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Montant réduit');
            $table->timestamps();
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
