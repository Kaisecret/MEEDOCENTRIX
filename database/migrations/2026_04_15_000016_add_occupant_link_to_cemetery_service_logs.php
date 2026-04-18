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
        if (! Schema::hasTable('cemetery_service_logs')) {
            return;
        }

        Schema::table('cemetery_service_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('cemetery_service_logs', 'occupant_record_id')) {
                $table->foreignId('occupant_record_id')
                    ->nullable()
                    ->after('cemetery_service_type_id')
                    ->constrained('cemetery_occupant_records')
                    ->nullOnDelete();
            }
        });

        DB::statement("
            UPDATE cemetery_service_logs sl
            INNER JOIN cemetery_occupant_records occ
                ON occ.cemetery_site_id = sl.cemetery_site_id
                AND UPPER(TRIM(occ.deceased_name)) = UPPER(TRIM(sl.deceased_name))
            INNER JOIN cemetery_plots pl
                ON pl.id = occ.cemetery_plot_id
                AND UPPER(TRIM(pl.plot_reference)) = UPPER(TRIM(sl.plot_reference))
            SET sl.occupant_record_id = occ.id
            WHERE sl.occupant_record_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cemetery_service_logs')) {
            return;
        }

        Schema::table('cemetery_service_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('cemetery_service_logs', 'occupant_record_id')) {
                $table->dropConstrainedForeignId('occupant_record_id');
            }
        });
    }
};

