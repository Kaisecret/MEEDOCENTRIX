<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('terminal_route_fares')) {
            Schema::create('terminal_route_fares', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('vehicle_kind', 80);
                $table->string('route_name', 150);
                $table->decimal('fare_amount', 12, 2)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'vehicle_kind', 'fare_amount'], 'idx_terminal_route_fares_active_kind_fare');
            });
        }

        if (Schema::hasTable('terminal_route_fares') && DB::table('terminal_route_fares')->count() === 0) {
            $now = now();

            DB::table('terminal_route_fares')->insert([
                ['code' => 'jeep_bugasong', 'vehicle_kind' => 'Jeep', 'route_name' => 'Bugasong', 'fare_amount' => 20.00, 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_lindero', 'vehicle_kind' => 'Jeep', 'route_name' => 'Lindero', 'fare_amount' => 20.00, 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_guinsangan', 'vehicle_kind' => 'Jeep', 'route_name' => 'Guinsang-an', 'fare_amount' => 20.00, 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_patnongon', 'vehicle_kind' => 'Jeep', 'route_name' => 'Patnongon', 'fare_amount' => 20.00, 'sort_order' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_sibalom', 'vehicle_kind' => 'Jeep', 'route_name' => 'Sibalom', 'fare_amount' => 20.00, 'sort_order' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_bugo', 'vehicle_kind' => 'Jeep', 'route_name' => 'Bugo', 'fare_amount' => 20.00, 'sort_order' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_san_remegio', 'vehicle_kind' => 'Jeep', 'route_name' => 'San Remegio', 'fare_amount' => 20.00, 'sort_order' => 70, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_dao', 'vehicle_kind' => 'Jeep', 'route_name' => 'Dao', 'fare_amount' => 35.00, 'sort_order' => 80, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_aniniy', 'vehicle_kind' => 'Jeep', 'route_name' => 'Anini-y', 'fare_amount' => 35.00, 'sort_order' => 90, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'jeep_valderrama', 'vehicle_kind' => 'Jeep', 'route_name' => 'Valderrama', 'fare_amount' => 35.00, 'sort_order' => 100, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'bus_ceres_iloilo', 'vehicle_kind' => 'Bus', 'route_name' => 'Ceres - Iloilo', 'fare_amount' => 60.00, 'sort_order' => 110, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'bus_roro_alps', 'vehicle_kind' => 'Bus', 'route_name' => 'Roro - ALPS', 'fare_amount' => 100.00, 'sort_order' => 120, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'bus_roro_ceres', 'vehicle_kind' => 'Bus', 'route_name' => 'Roro - Ceres', 'fare_amount' => 100.00, 'sort_order' => 130, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_route_fares');
    }
};

