<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budgets') || ! Schema::hasColumn('budgets', 'spent')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM `budgets` WHERE Field = 'spent'"))->first();

        if ($column && str_contains(strtolower($column->Default ?? ''), '0')) {
            return;
        }

        DB::table('budgets')->whereNull('spent')->update(['spent' => 0]);

        DB::statement('ALTER TABLE `budgets` MODIFY `spent` DECIMAL(15, 2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        if (! Schema::hasTable('budgets')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM `budgets` WHERE Field = 'spent'"))->first();

        if ($column && ($column->Default === null || $column->Default === '')) {
            return;
        }

        DB::statement('ALTER TABLE `budgets` MODIFY `spent` DECIMAL(15, 2) NOT NULL');
    }
};
