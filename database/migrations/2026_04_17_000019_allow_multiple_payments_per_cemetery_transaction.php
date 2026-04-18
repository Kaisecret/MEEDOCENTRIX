<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cemetery_payment_collections')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM cemetery_payment_collections'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('cemetery_payment_collections', function (Blueprint $table) use ($indexes): void {
            if (! in_array('idx_cemetery_payment_txn', $indexes, true)) {
                $table->index('cemetery_transaction_id', 'idx_cemetery_payment_txn');
            }

            if (in_array('uq_cemetery_payment_txn_once', $indexes, true)) {
                $table->dropUnique('uq_cemetery_payment_txn_once');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cemetery_payment_collections')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM cemetery_payment_collections'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('cemetery_payment_collections', function (Blueprint $table) use ($indexes): void {
            if (in_array('idx_cemetery_payment_txn', $indexes, true)) {
                $table->dropIndex('idx_cemetery_payment_txn');
            }

            if (! in_array('uq_cemetery_payment_txn_once', $indexes, true)) {
                $table->unique('cemetery_transaction_id', 'uq_cemetery_payment_txn_once');
            }
        });
    }
};
