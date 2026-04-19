<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('atrium_function_halls')) {
            Schema::create('atrium_function_halls', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 150);
                $table->integer('capacity')->nullable();
                $table->decimal('hourly_rate', 12, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('atrium_events')) {
            Schema::create('atrium_events', function (Blueprint $table): void {
                $table->id();
                $table->string('event_code', 30)->unique();
                $table->date('date_of_event');
                $table->time('start_time')->nullable();
                $table->text('event_details');
                $table->string('name_contact_person', 160);
                $table->string('contact_number', 60)->nullable();
                $table->foreignId('atrium_function_hall_id')
                    ->constrained('atrium_function_halls')
                    ->restrictOnDelete();
                $table->decimal('no_of_hours', 6, 2)->default(1);
                $table->decimal('hall_payment', 12, 2)->default(0);
                $table->decimal('miscellaneous_payment', 12, 2)->default(0);
                $table->decimal('accommodation_payment', 12, 2)->default(0);
                $table->decimal('actual_due', 12, 2)->default(0);
                $table->string('booking_status', 30)->default('reserved');
                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamps();

                $table->index(['date_of_event', 'booking_status'], 'idx_atrium_events_date_status');
                $table->index('atrium_function_hall_id', 'idx_atrium_events_hall');
            });
        }

        if (! Schema::hasTable('atrium_event_add_ons')) {
            Schema::create('atrium_event_add_ons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('atrium_event_id')
                    ->constrained('atrium_events')
                    ->cascadeOnDelete();
                $table->string('description', 200);
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();

                $table->index('atrium_event_id', 'idx_atrium_add_ons_event');
            });
        }

        if (! Schema::hasTable('atrium_event_payments')) {
            Schema::create('atrium_event_payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('atrium_event_id')
                    ->constrained('atrium_events')
                    ->cascadeOnDelete();
                $table->string('or_number', 60);
                $table->date('date_of_payment');
                $table->decimal('payment_amount', 12, 2);
                $table->string('payment_status', 30)->default('partial');
                $table->text('remarks')->nullable();
                $table->foreignId('recorded_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamps();

                $table->unique('or_number', 'uq_atrium_payments_or_number');
                $table->index(['atrium_event_id', 'date_of_payment'], 'idx_atrium_payments_event_date');
            });
        }

        if (! Schema::hasTable('atrium_supplies_orders')) {
            Schema::create('atrium_supplies_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('atrium_event_id')
                    ->constrained('atrium_events')
                    ->cascadeOnDelete();
                $table->string('time_needed', 60)->nullable();
                $table->text('requested_supplies');
                $table->string('request_status', 30)->default('pending');
                $table->text('remarks')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->foreignId('requested_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamps();

                $table->index(['atrium_event_id', 'request_status'], 'idx_atrium_supplies_event_status');
            });
        }

        $now = now();
        if (DB::table('atrium_function_halls')->count() === 0) {
            DB::table('atrium_function_halls')->insert([
                ['code' => 'ATR-MAIN', 'name' => 'Main Atrium Hall', 'capacity' => 500, 'hourly_rate' => 2500.00, 'description' => 'Main open atrium, grand functions', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'ATR-EAST', 'name' => 'East Wing Hall', 'capacity' => 180, 'hourly_rate' => 1500.00, 'description' => 'Meetings and exhibits', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'ATR-WEST', 'name' => 'West Wing Hall', 'capacity' => 150, 'hourly_rate' => 1200.00, 'description' => 'Programs and seminars', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'ATR-MEZZ', 'name' => 'Mezzanine Function Room', 'capacity' => 80, 'hourly_rate' => 800.00, 'description' => 'Small gatherings', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('atrium_supplies_orders');
        Schema::dropIfExists('atrium_event_payments');
        Schema::dropIfExists('atrium_event_add_ons');
        Schema::dropIfExists('atrium_events');
        Schema::dropIfExists('atrium_function_halls');
    }
};
