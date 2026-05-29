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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->decimal('discount_value', 10, 2);
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
        });

        Schema::dropIfExists('coupons');
    }
};
