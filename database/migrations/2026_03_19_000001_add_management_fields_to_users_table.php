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
        if (
            Schema::hasColumn('users', 'username') &&
            Schema::hasColumn('users', 'role') &&
            Schema::hasColumn('users', 'department') &&
            Schema::hasColumn('users', 'is_active')
        ) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique();
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('personnel');
            }

            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department', 100)->nullable();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('users', 'username')) {
                $columns[] = 'username';
            }

            if (Schema::hasColumn('users', 'role')) {
                $columns[] = 'role';
            }

            if (Schema::hasColumn('users', 'department')) {
                $columns[] = 'department';
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $columns[] = 'is_active';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
