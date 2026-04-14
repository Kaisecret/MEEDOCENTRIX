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
        if (! Schema::hasTable('market_stall_locations')) {
            Schema::create('market_stall_locations', function (Blueprint $table): void {
                $table->id();
                $table->string('location_code', 50)->unique();
                $table->string('location_name', 120);
                $table->string('zone', 120)->nullable();
                $table->string('floor_level', 60)->nullable();
                $table->text('remarks')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('market_stall_types')) {
            Schema::create('market_stall_types', function (Blueprint $table): void {
                $table->id();
                $table->string('type_name', 100)->unique();
                $table->string('description', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('market_stall_rates')) {
            Schema::create('market_stall_rates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('market_stall_location_id')->constrained('market_stall_locations')->cascadeOnDelete();
                $table->decimal('rate_amount', 12, 2);
                $table->date('effective_start_date');
                $table->date('effective_end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['market_stall_location_id', 'is_active', 'effective_start_date'], 'idx_market_stall_rate_lookup');
            });
        }

        if (! Schema::hasTable('market_stalls')) {
            Schema::create('market_stalls', function (Blueprint $table): void {
                $table->id();
                $table->string('stall_no', 60)->unique();
                $table->foreignId('market_stall_location_id')->constrained('market_stall_locations')->restrictOnDelete();
                $table->foreignId('market_stall_type_id')->constrained('market_stall_types')->restrictOnDelete();
                $table->decimal('dimension_sq_m', 10, 2)->nullable();
                $table->text('description')->nullable();
                $table->string('stall_status', 30)->default('vacant');
                $table->boolean('is_billable')->default(true);
                $table->timestamps();

                $table->index(['stall_status', 'market_stall_location_id'], 'idx_market_stall_status_location');
            });
        }

        if (! Schema::hasTable('market_tenants')) {
            Schema::create('market_tenants', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name', 120);
                $table->string('last_name', 120);
                $table->string('middle_name', 120)->nullable();
                $table->string('address', 255)->nullable();
                $table->string('contact_number', 60)->nullable();
                $table->string('business_name', 160)->nullable();
                $table->string('business_type', 120)->nullable();
                $table->string('mpo_control_no', 120)->nullable();
                $table->timestamps();

                $table->index(['last_name', 'first_name'], 'idx_market_tenants_name');
            });
        }

        if (! Schema::hasTable('market_stall_leases')) {
            Schema::create('market_stall_leases', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('market_stall_id')->constrained('market_stalls')->cascadeOnDelete();
                $table->foreignId('market_tenant_id')->constrained('market_tenants')->restrictOnDelete();
                $table->foreignId('market_stall_rate_id')->constrained('market_stall_rates')->restrictOnDelete();
                $table->string('contract_number', 90)->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('lease_status', 30)->default('active');
                $table->text('remarks')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['market_stall_id', 'lease_status', 'start_date'], 'idx_market_stall_lease_lookup');
            });
        }

        $now = now();

        if (DB::table('market_stall_types')->count() === 0) {
            DB::table('market_stall_types')->insert([
                ['type_name' => 'Dry Goods', 'description' => 'General merchandise and dry market goods', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_name' => 'Wet Market', 'description' => 'Fish, meat, and fresh produce section', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_name' => 'Vegetables', 'description' => 'Vegetable and fruit vendors', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_name' => 'Canteen', 'description' => 'Prepared food and eatery stalls', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_name' => 'Mixed Retail', 'description' => 'Mixed-category business operations', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (DB::table('market_stall_locations')->count() === 0) {
            DB::table('market_stall_locations')->insert([
                ['location_code' => 'T2-202', 'location_name' => 'Terminal Row 2', 'zone' => 'North Wing', 'floor_level' => 'Ground', 'remarks' => 'Reference area from legacy records', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['location_code' => 'A1-104', 'location_name' => 'Arcade Row 1', 'zone' => 'East Wing', 'floor_level' => 'Ground', 'remarks' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['location_code' => 'B2-018', 'location_name' => 'Block B Row 2', 'zone' => 'Central', 'floor_level' => 'Ground', 'remarks' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['location_code' => 'C1-031', 'location_name' => 'Cold Goods Strip', 'zone' => 'West Wing', 'floor_level' => 'Ground', 'remarks' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['location_code' => 'D3-011', 'location_name' => 'Daily Vendors Extension', 'zone' => 'South Wing', 'floor_level' => 'Ground', 'remarks' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);

            $locationRates = DB::table('market_stall_locations')
                ->select('id', 'location_code')
                ->get()
                ->map(static function ($location) use ($now): array {
                    $rate = match ($location->location_code) {
                        'T2-202' => 104.92,
                        'A1-104' => 92.50,
                        'B2-018' => 88.00,
                        'C1-031' => 110.00,
                        'D3-011' => 79.25,
                        default => 95.00,
                    };

                    return [
                        'market_stall_location_id' => $location->id,
                        'rate_amount' => $rate,
                        'effective_start_date' => now()->toDateString(),
                        'effective_end_date' => null,
                        'is_active' => 1,
                        'created_by_user_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })
                ->all();

            DB::table('market_stall_rates')->insert($locationRates);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_stall_leases');
        Schema::dropIfExists('market_tenants');
        Schema::dropIfExists('market_stalls');
        Schema::dropIfExists('market_stall_rates');
        Schema::dropIfExists('market_stall_types');
        Schema::dropIfExists('market_stall_locations');
    }
};

