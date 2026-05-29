<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Converts legacy string-based user columns to proper types.
     * No-op on fresh installs where create_users_table already uses booleans/dates.
     */
    public function up(): void
    {
        if (! $this->hasLegacyStringBooleans()) {
            return;
        }

        foreach (['is_active', 'notifications_enabled', 'email_notifications', 'push_notifications', 'sms_notifications'] as $column) {
            DB::table('users')
                ->whereIn($column, ['1', 'true', 'yes', 'on'])
                ->update([$column => '1']);

            DB::table('users')
                ->whereNotNull($column)
                ->whereNotIn($column, ['1'])
                ->update([$column => '0']);
        }

        DB::statement('ALTER TABLE `users` MODIFY `is_active` TINYINT(1) NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE `users` MODIFY `notifications_enabled` TINYINT(1) NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE `users` MODIFY `email_notifications` TINYINT(1) NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE `users` MODIFY `push_notifications` TINYINT(1) NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE `users` MODIFY `sms_notifications` TINYINT(1) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE `users` MODIFY `last_login_at` TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE `users` MODIFY `date_of_birth` DATE NULL DEFAULT NULL');

        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (! $this->hasLegacyStringBooleans()) {
            return;
        }

        DB::statement('ALTER TABLE `users` MODIFY `is_active` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `notifications_enabled` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `email_notifications` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `push_notifications` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `sms_notifications` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `last_login_at` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `date_of_birth` VARCHAR(191) NULL');

        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }

    private function hasLegacyStringBooleans(): bool
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'is_active')) {
            return false;
        }

        $type = Schema::getColumnType('users', 'is_active');

        return in_array($type, ['string', 'varchar'], true);
    }
};
