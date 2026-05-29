<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('accounts', ['user_id', 'account_number'])) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasIndex('accounts', ['account_number'])) {
                $table->dropUnique(['account_number']);
            }
            $table->unique(['user_id', 'account_number']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasIndex('accounts', ['user_id', 'account_number'])) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'account_number']);
            $table->unique('account_number');
        });
    }
};
