<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('terminal_vehicle_types')) {
            Schema::create('terminal_vehicle_types', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 120);
                $table->decimal('parking_fee_per_hour', 12, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('terminal_vehicles')) {
            Schema::create('terminal_vehicles', function (Blueprint $table): void {
                $table->id();
                $table->string('plate_number', 40)->unique();
                $table->string('operator_name', 160)->nullable();
                $table->foreignId('terminal_vehicle_type_id')
                    ->constrained('terminal_vehicle_types')
                    ->restrictOnDelete();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['terminal_vehicle_type_id', 'is_active'], 'idx_terminal_vehicles_type_active');
            });
        }

        if (! Schema::hasTable('terminal_parking_logs')) {
            Schema::create('terminal_parking_logs', function (Blueprint $table): void {
                $table->id();
                $table->string('log_number', 40)->unique();
                $table->foreignId('terminal_vehicle_id')
                    ->constrained('terminal_vehicles')
                    ->restrictOnDelete();
                $table->dateTime('entry_at');
                $table->dateTime('exit_at')->nullable();
                $table->foreignId('recorded_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamps();

                $table->index(['entry_at', 'exit_at'], 'idx_terminal_parking_range');
                $table->index('terminal_vehicle_id', 'idx_terminal_parking_vehicle');
            });
        }

        if (! Schema::hasTable('terminal_parking_payments')) {
            Schema::create('terminal_parking_payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('terminal_parking_log_id')
                    ->constrained('terminal_parking_logs')
                    ->cascadeOnDelete();
                $table->string('or_number', 60)->unique();
                $table->dateTime('payment_date');
                $table->decimal('parking_rate_snapshot', 12, 2)->default(0);
                $table->decimal('billed_hours_snapshot', 8, 2)->default(0);
                $table->decimal('billed_amount', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->string('payment_status', 30)->default('paid');
                $table->text('remarks')->nullable();
                $table->foreignId('recorded_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamps();

                $table->unique('terminal_parking_log_id', 'uq_terminal_payment_per_log');
                $table->index(['payment_date', 'payment_status'], 'idx_terminal_payment_date_status');
            });
        }

        $now = now();
        if (DB::table('terminal_vehicle_types')->count() === 0) {
            DB::table('terminal_vehicle_types')->insert([
                [
                    'code' => 'BUS',
                    'name' => 'Bus',
                    'parking_fee_per_hour' => 40.00,
                    'description' => 'Standard TFCO parking rate for buses',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'VAN',
                    'name' => 'Van',
                    'parking_fee_per_hour' => 30.00,
                    'description' => 'Standard TFCO parking rate for vans',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'TRIKE',
                    'name' => 'Tricycle',
                    'parking_fee_per_hour' => 20.00,
                    'description' => 'Standard TFCO parking rate for tricycles',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_parking_payments');
        Schema::dropIfExists('terminal_parking_logs');
        Schema::dropIfExists('terminal_vehicles');
        Schema::dropIfExists('terminal_vehicle_types');
    }
};
