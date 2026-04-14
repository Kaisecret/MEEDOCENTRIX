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
        if (! Schema::hasTable('collection_dispatches')) {
            Schema::create('collection_dispatches', function (Blueprint $table) {
                $table->id();
                $table->string('department_code', 50)->index();
                $table->foreignId('collector_user_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('period_type', 20)->nullable();
                $table->date('from_date')->nullable();
                $table->date('to_date')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 40)->default('sent')->index();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('collection_dispatch_items')) {
            Schema::create('collection_dispatch_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_dispatch_id')->constrained('collection_dispatches')->cascadeOnDelete();
                $table->foreignId('fishport_log_id')->constrained('fishport_logs')->cascadeOnDelete();
                $table->foreignId('payment_record_id')->nullable()->constrained('fishport_payment_records')->nullOnDelete();
                $table->decimal('amount_snapshot', 14, 2)->default(0);
                $table->string('status', 50)->default('sent')->index();
                $table->string('payer_name', 150)->nullable();
                $table->timestamp('collected_at')->nullable();
                $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('proof_image_path', 255)->nullable();
                $table->text('collector_note')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_note')->nullable();
                $table->timestamps();

                $table->index(['collection_dispatch_id', 'status'], 'dispatch_status_idx');
                $table->index(['fishport_log_id', 'status'], 'log_status_idx');
                $table->unique(['collection_dispatch_id', 'fishport_log_id'], 'dispatch_log_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_dispatch_items');
        Schema::dropIfExists('collection_dispatches');
    }
};
