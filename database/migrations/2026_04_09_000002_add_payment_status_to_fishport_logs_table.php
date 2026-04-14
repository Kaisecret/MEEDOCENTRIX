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
        Schema::table('fishport_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('fishport_logs', 'is_paid')) {
                $table->boolean('is_paid')->default(false)->after('remarks');
            }

            if (! Schema::hasColumn('fishport_logs', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('is_paid');
            }

            if (! Schema::hasColumn('fishport_logs', 'paid_by_user_id')) {
                $table->foreignId('paid_by_user_id')
                    ->nullable()
                    ->after('paid_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishport_logs', function (Blueprint $table) {
            if (Schema::hasColumn('fishport_logs', 'paid_by_user_id')) {
                $table->dropConstrainedForeignId('paid_by_user_id');
            }

            if (Schema::hasColumn('fishport_logs', 'paid_at')) {
                $table->dropColumn('paid_at');
            }

            if (Schema::hasColumn('fishport_logs', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};

