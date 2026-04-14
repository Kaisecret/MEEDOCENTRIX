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
        if (! Schema::hasTable('cemetery_transaction_types')) {
            Schema::create('cemetery_transaction_types', function (Blueprint $table): void {
                $table->id();
                $table->string('type_code', 60)->unique();
                $table->string('type_name', 140)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cemetery_transactions')) {
            Schema::create('cemetery_transactions', function (Blueprint $table): void {
                $table->id();
                $table->string('transaction_no', 40)->unique();
                $table->date('transaction_date');
                $table->foreignId('cemetery_site_id')->constrained('cemetery_sites')->restrictOnDelete();
                $table->foreignId('cemetery_category_id')->constrained('cemetery_categories')->restrictOnDelete();
                $table->foreignId('cemetery_transaction_type_id')->constrained('cemetery_transaction_types')->restrictOnDelete();
                $table->string('deceased_name', 190);
                $table->string('plot_reference', 80);
                $table->decimal('quantity', 10, 2)->nullable();
                $table->decimal('amount_due', 12, 2);
                $table->text('remarks')->nullable();
                $table->string('status', 30)->default('pending');
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['transaction_date', 'cemetery_site_id'], 'idx_cemetery_txn_date_site');
                $table->index(['cemetery_transaction_type_id', 'status'], 'idx_cemetery_txn_type_status');
            });
        }

        $now = now();

        if (DB::table('cemetery_transaction_types')->count() === 0) {
            DB::table('cemetery_transaction_types')->insert([
                ['type_code' => 'SINGLE_NICHE_PURCHASE', 'type_name' => 'Single Niche Purchase', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'LOT_PURCHASE', 'type_name' => 'Lot Purchase', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'ADDITIONAL_BURIAL', 'type_name' => 'Additional Burial', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'BURIAL_PERMIT', 'type_name' => 'Burial Permit', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'MAINTENANCE_FEE', 'type_name' => 'Maintenance Fee', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'RENEWAL', 'type_name' => 'Renewal', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'TRANSFER', 'type_name' => 'Transfer', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'EXHUMATION', 'type_name' => 'Exhumation', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['type_code' => 'OTHER', 'type_name' => 'Other Cemetery Service', 'description' => null, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemetery_transactions');
        Schema::dropIfExists('cemetery_transaction_types');
    }
};

