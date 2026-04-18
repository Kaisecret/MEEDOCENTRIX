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
        if (! Schema::hasTable('cemetery_transactions')) {
            return;
        }

        Schema::table('cemetery_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('cemetery_transactions', 'total_paid')) {
                $table->decimal('total_paid', 12, 2)->default(0)->after('amount_due');
            }
            if (! Schema::hasColumn('cemetery_transactions', 'remaining_balance')) {
                $table->decimal('remaining_balance', 12, 2)->default(0)->after('total_paid');
            }
        });

        DB::table('cemetery_transactions')
            ->select(['id', 'amount_due'])
            ->orderBy('id')
            ->chunkById(200, function ($transactions): void {
                foreach ($transactions as $transaction) {
                    $totalPaid = round((float) (DB::table('cemetery_payment_collections')
                        ->where('cemetery_transaction_id', $transaction->id)
                        ->sum('amount_paid')), 2);
                    $amountDue = round((float) ($transaction->amount_due ?? 0), 2);
                    $remaining = round(max($amountDue - $totalPaid, 0), 2);

                    DB::table('cemetery_transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'total_paid' => $totalPaid,
                            'remaining_balance' => $remaining,
                        ]);
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
            if (Schema::hasColumn('cemetery_transactions', 'remaining_balance')) {
                $table->dropColumn('remaining_balance');
            }
            if (Schema::hasColumn('cemetery_transactions', 'total_paid')) {
                $table->dropColumn('total_paid');
            }
        });
    }
};

