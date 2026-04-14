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
        Schema::create('fishport_payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fishport_log_id')
                ->unique()
                ->constrained('fishport_logs')
                ->cascadeOnDelete();
            $table->string('payment_number', 50)->unique();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('generated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        $summaries = DB::table('fishport_log_payments')
            ->selectRaw('fishport_log_id, SUM(total) as total_amount')
            ->groupBy('fishport_log_id')
            ->get();

        foreach ($summaries as $summary) {
            $logId = (int) $summary->fishport_log_id;
            if ($logId <= 0) {
                continue;
            }

            $paymentNumber = 'FP-PAY-' . str_pad((string) $logId, 6, '0', STR_PAD_LEFT);
            $log = DB::table('fishport_logs')->where('id', $logId)->first(['id', 'user_id', 'created_at', 'updated_at']);
            if (! $log) {
                continue;
            }

            DB::table('fishport_payment_records')->updateOrInsert(
                ['fishport_log_id' => $logId],
                [
                    'payment_number' => $paymentNumber,
                    'total_amount' => round((float) $summary->total_amount, 2),
                    'generated_by_user_id' => $log->user_id,
                    'generated_at' => $log->updated_at ?? $log->created_at,
                    'created_at' => $log->created_at ?? now(),
                    'updated_at' => $log->updated_at ?? now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishport_payment_records');
    }
};

