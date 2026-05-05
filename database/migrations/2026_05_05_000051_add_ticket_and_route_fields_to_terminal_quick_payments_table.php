<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('terminal_quick_payments')) {
            return;
        }

        Schema::table('terminal_quick_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('terminal_quick_payments', 'ticket_number')) {
                $table->string('ticket_number', 80)->nullable()->after('payer_name');
            }
            if (! Schema::hasColumn('terminal_quick_payments', 'vehicle_kind')) {
                $table->string('vehicle_kind', 20)->nullable()->after('ticket_number');
            }
            if (! Schema::hasColumn('terminal_quick_payments', 'route_name')) {
                $table->string('route_name', 120)->nullable()->after('vehicle_kind');
            }
            if (! Schema::hasColumn('terminal_quick_payments', 'route_code')) {
                $table->string('route_code', 80)->nullable()->after('route_name');
            }
        });

        Schema::table('terminal_quick_payments', function (Blueprint $table): void {
            $table->unique('ticket_number', 'uq_terminal_quick_payments_ticket_number');
            $table->index('route_code', 'idx_terminal_quick_payments_route_code');
            $table->index('vehicle_kind', 'idx_terminal_quick_payments_vehicle_kind');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('terminal_quick_payments')) {
            return;
        }

        Schema::table('terminal_quick_payments', function (Blueprint $table): void {
            $table->dropUnique('uq_terminal_quick_payments_ticket_number');
            $table->dropIndex('idx_terminal_quick_payments_route_code');
            $table->dropIndex('idx_terminal_quick_payments_vehicle_kind');

            if (Schema::hasColumn('terminal_quick_payments', 'route_code')) {
                $table->dropColumn('route_code');
            }
            if (Schema::hasColumn('terminal_quick_payments', 'route_name')) {
                $table->dropColumn('route_name');
            }
            if (Schema::hasColumn('terminal_quick_payments', 'vehicle_kind')) {
                $table->dropColumn('vehicle_kind');
            }
            if (Schema::hasColumn('terminal_quick_payments', 'ticket_number')) {
                $table->dropColumn('ticket_number');
            }
        });
    }
};