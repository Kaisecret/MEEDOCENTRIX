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
        Schema::create('fishport_vessels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('owner_name', 150)->nullable();
            $table->string('vessel_type', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fishport_origins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fishport_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        Schema::create('fishport_commodity_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        Schema::create('fishport_commodities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->foreignId('classification_id')
                ->constrained('fishport_commodity_classifications')
                ->restrictOnDelete();
            $table->foreignId('default_unit_id')
                ->nullable()
                ->constrained('fishport_units')
                ->nullOnDelete();
            $table->decimal('default_conversion', 12, 4)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fishport_payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 150)->unique();
            $table->decimal('default_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fishport_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_number', 50)->unique();
            $table->date('log_date');
            $table->time('log_time');
            $table->string('arr_dep', 3);
            $table->foreignId('fishport_vessel_id')
                ->constrained('fishport_vessels')
                ->restrictOnDelete();
            $table->foreignId('fishport_origin_id')
                ->constrained('fishport_origins')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('fishport_log_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishport_log_id')
                ->constrained('fishport_logs')
                ->cascadeOnDelete();
            $table->foreignId('fishport_commodity_id')
                ->constrained('fishport_commodities')
                ->restrictOnDelete();
            $table->foreignId('unit_id')
                ->constrained('fishport_units')
                ->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_conversion', 12, 4)->default(1);
            $table->decimal('volume', 14, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('fishport_log_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishport_log_id')
                ->constrained('fishport_logs')
                ->cascadeOnDelete();
            $table->foreignId('fishport_payment_type_id')
                ->constrained('fishport_payment_types')
                ->restrictOnDelete();
            $table->decimal('fee', 12, 2)->default(0);
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['fishport_log_id', 'fishport_payment_type_id'], 'fishport_log_payment_unique');
        });

        $now = now();

        DB::table('fishport_units')->insert([
            ['name' => 'Tub', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Box', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kilogram', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Block', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('fishport_commodity_classifications')->insert([
            ['name' => 'Marine', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ice', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('fishport_origins')->insert([
            ['name' => 'Sulu Sea', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'San Jose', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cocoro Island', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cuyo Island', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maybato', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('fishport_vessels')->insert([
            ['name' => 'MB Ziah & Noah', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MB Jessa', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MB San Rafael', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MB Arlyn Mae', 'created_at' => $now, 'updated_at' => $now],
            ['name' => '4W Vehicle ACDI', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $unitIds = DB::table('fishport_units')->pluck('id', 'name');
        $classificationIds = DB::table('fishport_commodity_classifications')->pluck('id', 'name');

        DB::table('fishport_commodities')->insert([
            [
                'name' => 'Aloy (Bullet Fish)',
                'classification_id' => $classificationIds['Marine'],
                'default_unit_id' => $unitIds['Tub'],
                'default_conversion' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bisugo (Treadfin Bream)',
                'classification_id' => $classificationIds['Marine'],
                'default_unit_id' => $unitIds['Tub'],
                'default_conversion' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kanturayan',
                'classification_id' => $classificationIds['Marine'],
                'default_unit_id' => $unitIds['Kilogram'],
                'default_conversion' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marot',
                'classification_id' => $classificationIds['Marine'],
                'default_unit_id' => $unitIds['Box'],
                'default_conversion' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marine (Mixed)',
                'classification_id' => $classificationIds['Marine'],
                'default_unit_id' => $unitIds['Box'],
                'default_conversion' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Ice',
                'classification_id' => $classificationIds['Ice'],
                'default_unit_id' => $unitIds['Block'],
                'default_conversion' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('fishport_payment_types')->insert([
            ['code' => 'ENTRANCE', 'name' => 'Entrance Fee', 'default_fee' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'DOCKING', 'name' => 'Docking', 'default_fee' => 75, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'UNLOADING', 'name' => 'Unloading Fee', 'default_fee' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'TRANSSHIPMENT', 'name' => 'Transshipment', 'default_fee' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ICE_CONVEYANCE', 'name' => 'Ice Conveyance Fee', 'default_fee' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishport_log_payments');
        Schema::dropIfExists('fishport_log_items');
        Schema::dropIfExists('fishport_logs');
        Schema::dropIfExists('fishport_payment_types');
        Schema::dropIfExists('fishport_commodities');
        Schema::dropIfExists('fishport_commodity_classifications');
        Schema::dropIfExists('fishport_units');
        Schema::dropIfExists('fishport_origins');
        Schema::dropIfExists('fishport_vessels');
    }
};

