<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('market_stall_leases')) {
            return;
        }

        Schema::table('market_stall_leases', function (Blueprint $table): void {
            if (! Schema::hasColumn('market_stall_leases', 'collector_user_id')) {
                $table->foreignId('collector_user_id')
                    ->nullable()
                    ->after('created_by_user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('market_stall_leases', 'collector_assigned_at')) {
                $table->timestamp('collector_assigned_at')
                    ->nullable()
                    ->after('collector_user_id');
            }
            if (! Schema::hasColumn('market_stall_leases', 'collector_assigned_by_user_id')) {
                $table->foreignId('collector_assigned_by_user_id')
                    ->nullable()
                    ->after('collector_assigned_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('market_stall_leases')) {
            return;
        }

        Schema::table('market_stall_leases', function (Blueprint $table): void {
            if (Schema::hasColumn('market_stall_leases', 'collector_assigned_by_user_id')) {
                $table->dropConstrainedForeignId('collector_assigned_by_user_id');
            }
            if (Schema::hasColumn('market_stall_leases', 'collector_assigned_at')) {
                $table->dropColumn('collector_assigned_at');
            }
            if (Schema::hasColumn('market_stall_leases', 'collector_user_id')) {
                $table->dropConstrainedForeignId('collector_user_id');
            }
        });
    }
};

