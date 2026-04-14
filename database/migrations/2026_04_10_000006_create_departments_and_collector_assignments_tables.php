<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 120);
                $table->boolean('allows_collectors')->default(false);
                $table->boolean('direct_payment_only')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $now = now();

        DB::table('departments')->upsert([
            [
                'code' => 'fishport',
                'name' => 'Fishport',
                'allows_collectors' => true,
                'direct_payment_only' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'market',
                'name' => 'Public Market',
                'allows_collectors' => true,
                'direct_payment_only' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'atrium',
                'name' => 'Atrium Hall',
                'allows_collectors' => true,
                'direct_payment_only' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'cemetery',
                'name' => 'Cemetery',
                'allows_collectors' => false,
                'direct_payment_only' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'terminal',
                'name' => 'Terminal',
                'allows_collectors' => false,
                'direct_payment_only' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code'], ['name', 'allows_collectors', 'direct_payment_only', 'is_active', 'updated_at']);

        if (! Schema::hasTable('collector_department_assignments')) {
            Schema::create('collector_department_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collector_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
                $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique('collector_user_id');
                $table->index('department_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collector_department_assignments');
        Schema::dropIfExists('departments');
    }
};
