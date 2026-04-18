<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cemetery_service_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('cemetery_service_logs', 'suggested_transaction_type_code')) {
                $table->string('suggested_transaction_type_code', 60)
                    ->nullable()
                    ->after('cemetery_service_type_id');
            }

            if (! Schema::hasColumn('cemetery_service_logs', 'suggested_amount_due')) {
                $table->decimal('suggested_amount_due', 12, 2)
                    ->nullable()
                    ->after('suggested_transaction_type_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cemetery_service_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('cemetery_service_logs', 'suggested_amount_due')) {
                $table->dropColumn('suggested_amount_due');
            }

            if (Schema::hasColumn('cemetery_service_logs', 'suggested_transaction_type_code')) {
                $table->dropColumn('suggested_transaction_type_code');
            }
        });
    }
};
