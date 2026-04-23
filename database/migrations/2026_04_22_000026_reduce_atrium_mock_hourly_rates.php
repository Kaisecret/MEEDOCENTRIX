<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('atrium_function_halls')) {
            return;
        }

        $now = now();

        $rates = [
            'ATR-MAIN' => 1200.00,
            'ATR-EAST' => 900.00,
            'ATR-WEST' => 800.00,
            'ATR-MEZZ' => 600.00,
        ];

        foreach ($rates as $code => $hourlyRate) {
            DB::table('atrium_function_halls')
                ->where('code', $code)
                ->update([
                    'hourly_rate' => $hourlyRate,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('atrium_function_halls')) {
            return;
        }

        $now = now();

        $previousRates = [
            'ATR-MAIN' => 2500.00,
            'ATR-EAST' => 1500.00,
            'ATR-WEST' => 1200.00,
            'ATR-MEZZ' => 800.00,
        ];

        foreach ($previousRates as $code => $hourlyRate) {
            DB::table('atrium_function_halls')
                ->where('code', $code)
                ->update([
                    'hourly_rate' => $hourlyRate,
                    'updated_at' => $now,
                ]);
        }
    }
};
