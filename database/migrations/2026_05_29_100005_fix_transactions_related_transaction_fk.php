<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['related_transaction_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('related_transaction_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['related_transaction_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('related_transaction_id')
                ->references('id')
                ->on('transactions')
                ->cascadeOnDelete();
        });
    }
};
