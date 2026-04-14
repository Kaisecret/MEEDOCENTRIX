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
        if (! Schema::hasTable('cemetery_transactions')) {
            return;
        }

        Schema::table('cemetery_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('cemetery_transactions', 'occupant_record_id')) {
                $table->foreignId('occupant_record_id')
                    ->nullable()
                    ->after('cemetery_transaction_type_id')
                    ->constrained('cemetery_occupant_records')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('cemetery_transactions', 'service_log_id')) {
                $table->foreignId('service_log_id')
                    ->nullable()
                    ->after('occupant_record_id')
                    ->constrained('cemetery_service_logs')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cemetery_transactions')) {
            return;
        }

        Schema::table('cemetery_transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('cemetery_transactions', 'service_log_id')) {
                $table->dropConstrainedForeignId('service_log_id');
            }

            if (Schema::hasColumn('cemetery_transactions', 'occupant_record_id')) {
                $table->dropConstrainedForeignId('occupant_record_id');
            }
        });
    }
};
