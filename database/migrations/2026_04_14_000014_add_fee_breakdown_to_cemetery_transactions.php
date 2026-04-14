<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('cemetery_transactions')) {
            return;
        }

        Schema::table('cemetery_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('cemetery_transactions', 'maintenance_type')) {
                $table->string('maintenance_type', 30)->nullable()->after('amount_due');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'maintenance_years')) {
                $table->unsignedSmallInteger('maintenance_years')->nullable()->after('maintenance_type');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'has_burial_permit')) {
                $table->boolean('has_burial_permit')->default(false)->after('maintenance_years');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'base_fee')) {
                $table->decimal('base_fee', 12, 2)->default(0)->after('has_burial_permit');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'maintenance_fee')) {
                $table->decimal('maintenance_fee', 12, 2)->default(0)->after('base_fee');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'burial_permit_fee')) {
                $table->decimal('burial_permit_fee', 12, 2)->default(0)->after('maintenance_fee');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'other_applicable_fee')) {
                $table->decimal('other_applicable_fee', 12, 2)->default(0)->after('burial_permit_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cemetery_transactions')) {
            return;
        }

        Schema::table('cemetery_transactions', function (Blueprint $table): void {
            $columns = [
                'maintenance_type',
                'maintenance_years',
                'has_burial_permit',
                'base_fee',
                'maintenance_fee',
                'burial_permit_fee',
                'other_applicable_fee',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('cemetery_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
