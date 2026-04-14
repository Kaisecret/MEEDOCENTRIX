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
        if (! Schema::hasTable('cemetery_service_types')) {
            Schema::create('cemetery_service_types', function (Blueprint $table): void {
                $table->id();
                $table->string('type_code', 50)->unique();
                $table->string('type_name', 120)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cemetery_service_logs')) {
            Schema::create('cemetery_service_logs', function (Blueprint $table): void {
                $table->id();
                $table->string('log_no', 40)->unique();
                $table->date('service_date');
                $table->foreignId('cemetery_site_id')->constrained('cemetery_sites')->restrictOnDelete();
                $table->foreignId('cemetery_service_type_id')->constrained('cemetery_service_types')->restrictOnDelete();
                $table->string('deceased_name', 190);
                $table->string('plot_reference', 80);
                $table->text('details')->nullable();
                $table->string('processed_by', 160);
                $table->text('remarks')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['service_date', 'cemetery_site_id'], 'idx_cemetery_service_date_site');
                $table->index(['cemetery_service_type_id', 'service_date'], 'idx_cemetery_service_type_date');
            });
        }

        $now = now();

        if (DB::table('cemetery_service_types')->count() === 0) {
            DB::table('cemetery_service_types')->insert([
                ['type_code' => 'INTERMENT', 'type_name' => 'Interment', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'BURIAL', 'type_name' => 'Burial', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'RENEWAL', 'type_name' => 'Renewal', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'MAINTENANCE_UPDATE', 'type_name' => 'Maintenance Update', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'TRANSFER', 'type_name' => 'Transfer', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'EXHUMATION', 'type_name' => 'Exhumation', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'RECORD_CORRECTION', 'type_name' => 'Record Correction', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemetery_service_logs');
        Schema::dropIfExists('cemetery_service_types');
    }
};

