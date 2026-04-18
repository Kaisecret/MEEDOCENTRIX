<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('market_stall_types')) {
            return;
        }

        Schema::table('market_stall_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('market_stall_types', 'default_rate')) {
                $table->decimal('default_rate', 12, 2)->default(0)->after('description');
            }
            if (! Schema::hasColumn('market_stall_types', 'rate_notes')) {
                $table->text('rate_notes')->nullable()->after('default_rate');
            }
        });

        $now = now();
        $typeRates = [
            ['type_name' => 'Checherias', 'default_rate' => 66.82, 'rate_notes' => 'Center: 66.82-63.25; Entrance: 76.66; Blg.1: 41.31-87.82; Blg.2: 29.73'],
            ['type_name' => 'Pastries', 'default_rate' => 82.96, 'rate_notes' => null],
            ['type_name' => 'Relief Goods', 'default_rate' => 106.92, 'rate_notes' => null],
            ['type_name' => 'North terminal (kiosk)', 'default_rate' => 63.60, 'rate_notes' => null],
            ['type_name' => 'Fruits section', 'default_rate' => 35.64, 'rate_notes' => 'Per stall reference'],
            ['type_name' => 'South Terminal (kiosk)', 'default_rate' => 50.00, 'rate_notes' => null],
            ['type_name' => 'Sari-sari section', 'default_rate' => 43.20, 'rate_notes' => null],
            ['type_name' => 'Grains & Cereal (Bugasan)', 'default_rate' => 104.92, 'rate_notes' => null],
            ['type_name' => 'Dried fish (blg.1)', 'default_rate' => 82.82, 'rate_notes' => null],
            ['type_name' => 'Salt and sugar (blg.2)', 'default_rate' => 45.18, 'rate_notes' => null],
            ['type_name' => 'Frozen Foods (blg.1)', 'default_rate' => 62.25, 'rate_notes' => null],
            ['type_name' => 'Fast Food (center)', 'default_rate' => 56.93, 'rate_notes' => null],
            ['type_name' => 'Dry goods (RTW, side blg.1)', 'default_rate' => 71.28, 'rate_notes' => null],
            ['type_name' => 'Blg.3 Restaurant', 'default_rate' => 63.50, 'rate_notes' => 'Range: 63.50-160.64; varies by dimension'],
            ['type_name' => 'Blg.3 General Merch.', 'default_rate' => 195.20, 'rate_notes' => null],
            ['type_name' => 'Vegetables and Fruit (blg.2)', 'default_rate' => 15.00, 'rate_notes' => 'Per table'],
        ];

        foreach ($typeRates as $row) {
            $existingId = DB::table('market_stall_types')
                ->where('type_name', $row['type_name'])
                ->value('id');

            if ($existingId) {
                DB::table('market_stall_types')
                    ->where('id', $existingId)
                    ->update([
                        'default_rate' => $row['default_rate'],
                        'rate_notes' => $row['rate_notes'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            DB::table('market_stall_types')->insert([
                'type_name' => $row['type_name'],
                'description' => 'Configured from market stall rate matrix',
                'default_rate' => $row['default_rate'],
                'rate_notes' => $row['rate_notes'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('market_stall_types')) {
            return;
        }

        Schema::table('market_stall_types', function (Blueprint $table): void {
            if (Schema::hasColumn('market_stall_types', 'rate_notes')) {
                $table->dropColumn('rate_notes');
            }
            if (Schema::hasColumn('market_stall_types', 'default_rate')) {
                $table->dropColumn('default_rate');
            }
        });
    }
};

