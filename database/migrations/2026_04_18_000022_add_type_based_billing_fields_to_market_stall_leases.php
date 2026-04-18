<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('market_stall_leases')) {
            return;
        }

        Schema::table('market_stall_leases', function (Blueprint $table): void {
            if (! Schema::hasColumn('market_stall_leases', 'selected_type_rates')) {
                $table->json('selected_type_rates')->nullable()->after('market_stall_rate_id');
            }
            if (! Schema::hasColumn('market_stall_leases', 'billing_period')) {
                $table->string('billing_period', 20)->default('monthly')->after('selected_type_rates');
            }
            if (! Schema::hasColumn('market_stall_leases', 'billing_cycles')) {
                $table->unsignedInteger('billing_cycles')->default(1)->after('billing_period');
            }
            if (! Schema::hasColumn('market_stall_leases', 'rate_multiplier')) {
                $table->decimal('rate_multiplier', 12, 2)->default(1)->after('billing_cycles');
            }
            if (! Schema::hasColumn('market_stall_leases', 'computed_rate_amount')) {
                $table->decimal('computed_rate_amount', 12, 2)->default(0)->after('rate_multiplier');
            }
        });

        $leases = DB::table('market_stall_leases as lease')
            ->leftJoin('market_stalls as stall', 'stall.id', '=', 'lease.market_stall_id')
            ->leftJoin('market_stall_types as type', 'type.id', '=', 'stall.market_stall_type_id')
            ->leftJoin('market_stall_rates as rate', 'rate.id', '=', 'lease.market_stall_rate_id')
            ->select([
                'lease.id',
                'stall.market_stall_type_id as type_id',
                'type.type_name',
                'type.default_rate',
                'type.rate_notes',
                'rate.rate_amount',
            ])
            ->get();

        foreach ($leases as $lease) {
            $selectedTypeRates = [];
            if (! empty($lease->type_id)) {
                $selectedTypeRates[] = [
                    'id' => (int) $lease->type_id,
                    'name' => (string) ($lease->type_name ?? 'Unknown'),
                    'base_rate' => round((float) ($lease->default_rate ?? 0), 2),
                    'notes' => $lease->rate_notes ? (string) $lease->rate_notes : null,
                ];
            }

            DB::table('market_stall_leases')
                ->where('id', $lease->id)
                ->update([
                    'selected_type_rates' => ! empty($selectedTypeRates) ? json_encode($selectedTypeRates, JSON_UNESCAPED_UNICODE) : null,
                    'billing_period' => 'monthly',
                    'billing_cycles' => 1,
                    'rate_multiplier' => 1,
                    'computed_rate_amount' => round((float) ($lease->rate_amount ?? 0), 2),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('market_stall_leases')) {
            return;
        }

        Schema::table('market_stall_leases', function (Blueprint $table): void {
            if (Schema::hasColumn('market_stall_leases', 'computed_rate_amount')) {
                $table->dropColumn('computed_rate_amount');
            }
            if (Schema::hasColumn('market_stall_leases', 'rate_multiplier')) {
                $table->dropColumn('rate_multiplier');
            }
            if (Schema::hasColumn('market_stall_leases', 'billing_cycles')) {
                $table->dropColumn('billing_cycles');
            }
            if (Schema::hasColumn('market_stall_leases', 'billing_period')) {
                $table->dropColumn('billing_period');
            }
            if (Schema::hasColumn('market_stall_leases', 'selected_type_rates')) {
                $table->dropColumn('selected_type_rates');
            }
        });
    }
};

