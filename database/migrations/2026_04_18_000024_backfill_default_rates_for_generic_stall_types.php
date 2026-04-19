<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('market_stall_types') || ! Schema::hasColumn('market_stall_types', 'default_rate')) {
            return;
        }

        $now = now();

        $genericRates = [
            ['type_name' => 'Dry Goods',     'default_rate' => 71.28,  'rate_notes' => 'Aligned with Dry goods (RTW, side blg.1) matrix rate'],
            ['type_name' => 'Wet Market',    'default_rate' => 72.54,  'rate_notes' => 'Avg of Dried fish (blg.1) and Frozen Foods (blg.1)'],
            ['type_name' => 'Vegetables',    'default_rate' => 15.00,  'rate_notes' => 'Aligned with Vegetables and Fruit (blg.2) per-table rate'],
            ['type_name' => 'Canteen',       'default_rate' => 56.93,  'rate_notes' => 'Aligned with Fast Food (center) matrix rate'],
            ['type_name' => 'Mixed Retail',  'default_rate' => 119.20, 'rate_notes' => 'Avg of Sari-sari section and Blg.3 General Merch'],
        ];

        foreach ($genericRates as $row) {
            $existingId = DB::table('market_stall_types')
                ->where('type_name', $row['type_name'])
                ->value('id');

            if (! $existingId) {
                continue;
            }

            DB::table('market_stall_types')
                ->where('id', $existingId)
                ->where(function ($query): void {
                    $query->whereNull('default_rate')->orWhere('default_rate', 0);
                })
                ->update([
                    'default_rate' => $row['default_rate'],
                    'rate_notes' => $row['rate_notes'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('market_stall_types') || ! Schema::hasColumn('market_stall_types', 'default_rate')) {
            return;
        }

        $names = ['Dry Goods', 'Wet Market', 'Vegetables', 'Canteen', 'Mixed Retail'];
        DB::table('market_stall_types')
            ->whereIn('type_name', $names)
            ->update(['default_rate' => 0, 'rate_notes' => null]);
    }
};
