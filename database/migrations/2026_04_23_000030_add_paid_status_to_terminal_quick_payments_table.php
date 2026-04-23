<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('terminal_quick_payments')) {
            return;
        }

        Schema::table('terminal_quick_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('terminal_quick_payments', 'is_paid')) {
                $table->boolean('is_paid')->default(false)->after('payment_date');
            }
            if (! Schema::hasColumn('terminal_quick_payments', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('is_paid');
            }
            if (! Schema::hasColumn('terminal_quick_payments', 'paid_by_user_id')) {
                $table->foreignId('paid_by_user_id')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
            }
        });

        DB::table('terminal_quick_payments')
            ->whereNull('is_paid')
            ->update(['is_paid' => false]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('terminal_quick_payments')) {
            return;
        }

        Schema::table('terminal_quick_payments', function (Blueprint $table): void {
            if (Schema::hasColumn('terminal_quick_payments', 'paid_by_user_id')) {
                $table->dropConstrainedForeignId('paid_by_user_id');
            }
            if (Schema::hasColumn('terminal_quick_payments', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('terminal_quick_payments', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};

