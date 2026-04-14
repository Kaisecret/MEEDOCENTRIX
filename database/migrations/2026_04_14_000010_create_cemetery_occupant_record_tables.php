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
        if (! Schema::hasTable('cemetery_sites')) {
            Schema::create('cemetery_sites', function (Blueprint $table): void {
                $table->id();
                $table->string('site_code', 40)->unique();
                $table->string('site_name', 160)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cemetery_categories')) {
            Schema::create('cemetery_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('category_code', 40)->unique();
                $table->string('category_name', 120)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cemetery_contacts')) {
            Schema::create('cemetery_contacts', function (Blueprint $table): void {
                $table->id();
                $table->string('contact_person', 160);
                $table->string('contact_number', 60)->nullable();
                $table->string('address', 255)->nullable();
                $table->timestamps();

                $table->index(['contact_person', 'contact_number'], 'idx_cemetery_contact_lookup');
            });
        }

        if (! Schema::hasTable('cemetery_plots')) {
            Schema::create('cemetery_plots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('cemetery_site_id')->constrained('cemetery_sites')->restrictOnDelete();
                $table->foreignId('cemetery_category_id')->constrained('cemetery_categories')->restrictOnDelete();
                $table->string('plot_reference', 80);
                $table->string('plot_type', 20)->default('niche');
                $table->boolean('is_occupied')->default(false);
                $table->boolean('is_active')->default(true);
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['cemetery_site_id', 'plot_reference'], 'uq_cemetery_plot_per_site');
                $table->index(['cemetery_site_id', 'cemetery_category_id', 'is_occupied'], 'idx_cemetery_plot_occupancy');
            });
        }

        if (! Schema::hasTable('cemetery_occupant_records')) {
            Schema::create('cemetery_occupant_records', function (Blueprint $table): void {
                $table->id();
                $table->string('record_no', 40)->unique();
                $table->foreignId('cemetery_site_id')->constrained('cemetery_sites')->restrictOnDelete();
                $table->foreignId('cemetery_category_id')->constrained('cemetery_categories')->restrictOnDelete();
                $table->foreignId('cemetery_plot_id')->constrained('cemetery_plots')->restrictOnDelete();
                $table->foreignId('cemetery_contact_id')->constrained('cemetery_contacts')->restrictOnDelete();
                $table->string('deceased_name', 190);
                $table->date('date_of_interment');
                $table->text('remarks')->nullable();
                $table->string('status', 30)->default('active');
                $table->string('maintenance_fee_status', 30)->default('unpaid');
                $table->date('coverage_start_date')->nullable();
                $table->date('coverage_end_date')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['status', 'maintenance_fee_status'], 'idx_cemetery_occ_status');
                $table->index('date_of_interment', 'idx_cemetery_occ_interment_date');
            });
        }

        $now = now();

        if (DB::table('cemetery_sites')->count() === 0) {
            DB::table('cemetery_sites')->insert([
                ['site_code' => 'SJM', 'site_name' => 'San Jose Memorial (Binirayan)', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['site_code' => 'OMC', 'site_name' => 'Old Municipal Cemetery', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['site_code' => 'NMC', 'site_name' => 'New Municipal Cemetery', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['site_code' => 'SPMC', 'site_name' => 'San Pedro Municipal Cemetery', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (DB::table('cemetery_categories')->count() === 0) {
            DB::table('cemetery_categories')->insert([
                ['category_code' => 'REGULAR', 'category_name' => 'Regular', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['category_code' => 'REGULAR_LARGE', 'category_name' => 'Regular Large Size', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['category_code' => 'INFANT', 'category_name' => 'Infant', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['category_code' => 'COLUMBARIUM', 'category_name' => 'Columbarium', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['category_code' => 'MAUSOLEUM_PLOT', 'category_name' => 'Mausoleum Plot', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['category_code' => 'FAMILY_PLOT', 'category_name' => 'Family Plot', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemetery_occupant_records');
        Schema::dropIfExists('cemetery_plots');
        Schema::dropIfExists('cemetery_contacts');
        Schema::dropIfExists('cemetery_categories');
        Schema::dropIfExists('cemetery_sites');
    }
};

