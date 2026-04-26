<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cemetery_fee_rules')) {
            Schema::create('cemetery_fee_rules', function (Blueprint $table): void {
                $table->id();
                $table->string('fee_key', 100)->unique();
                $table->string('label', 160);
                $table->text('description')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['is_active', 'sort_order'], 'idx_cemetery_fee_rules_active_sort');
            });
        }

        $now = now();
        $rules = [
            ['base.single_niche.sjm.infant', 'SJM Infant Single Niche', 'Base fee for San Jose Memorial infant single niche purchase.', 5000.00, 10],
            ['base.single_niche.sjm.regular', 'SJM Regular Single Niche', 'Base fee for San Jose Memorial regular single niche purchase.', 10000.00, 20],
            ['base.single_niche.sjm.regular_large', 'SJM Regular Large Single Niche', 'Base fee for San Jose Memorial regular large single niche purchase.', 10000.00, 30],
            ['base.single_niche.nmc.columbarium', 'NMC Columbarium Single Niche', 'Base fee for New Municipal Cemetery columbarium single niche purchase.', 5000.00, 40],
            ['base.single_niche.nmc.infant', 'NMC Infant Single Niche', 'Base fee for New Municipal Cemetery infant single niche purchase.', 5000.00, 50],
            ['base.additional_burial.omc', 'OMC Additional Burial', 'Base fee for Old Municipal Cemetery additional burial.', 5000.00, 60],
            ['base.additional_burial.nmc', 'NMC Additional Burial', 'Base fee for New Municipal Cemetery additional burial.', 5000.00, 70],
            ['base.additional_burial.spmc', 'SPMC Additional Burial', 'Base fee for San Pedro Municipal Cemetery additional burial.', 5000.00, 80],
            ['base.lot_purchase.spmc', 'SPMC Lot Purchase', 'Base fee for San Pedro Municipal Cemetery lot purchase.', 10000.00, 90],
            ['base.burial_permit', 'Burial Permit Transaction', 'Base amount when the transaction itself is a burial permit.', 300.00, 100],
            ['base.exhumation', 'Exhumation', 'Base fee for exhumation or kalkal transaction.', 200.00, 110],
            ['base.transfer', 'Transfer', 'Base fee for transfer transaction before additional flexible fee.', 300.00, 120],
            ['base.other', 'Other Cemetery Service', 'Base fee for other cemetery service before additional flexible fee.', 300.00, 130],
            ['permit.standard', 'Burial Permit Add-on', 'Burial permit add-on fee applied to qualifying transactions.', 300.00, 140],
            ['maintenance.yearly', 'Yearly Maintenance', 'Maintenance fee per covered year.', 300.00, 150],
            ['maintenance.five_year_fixed', 'Five-Year Maintenance', 'Fixed five-year maintenance fee.', 1500.00, 160],
        ];

        foreach ($rules as [$key, $label, $description, $amount, $sortOrder]) {
            $exists = DB::table('cemetery_fee_rules')->where('fee_key', $key)->exists();
            $payload = [
                'label' => $label,
                'description' => $description,
                'amount' => $amount,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('cemetery_fee_rules')
                    ->where('fee_key', $key)
                    ->update($payload);
            } else {
                DB::table('cemetery_fee_rules')->insert([
                    'fee_key' => $key,
                    'label' => $label,
                    'description' => $description,
                    'amount' => $amount,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cemetery_fee_rules');
    }
};
