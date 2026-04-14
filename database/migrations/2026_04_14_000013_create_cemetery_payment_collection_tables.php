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
        if (! Schema::hasTable('cemetery_payment_collections')) {
            Schema::create('cemetery_payment_collections', function (Blueprint $table): void {
                $table->id();
                $table->string('payment_no', 40)->unique();
                $table->foreignId('cemetery_transaction_id')->constrained('cemetery_transactions')->restrictOnDelete();
                $table->foreignId('cemetery_contact_id')->nullable()->constrained('cemetery_contacts')->nullOnDelete();
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('official_receipt_no', 60)->nullable()->unique();
                $table->date('payment_date')->nullable();
                $table->date('coverage_start_date')->nullable();
                $table->date('coverage_end_date')->nullable();
                $table->string('payment_status', 30)->default('unpaid');
                $table->text('remarks')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique('cemetery_transaction_id', 'uq_cemetery_payment_txn_once');
                $table->index(['payment_status', 'payment_date'], 'idx_cemetery_payment_status_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cemetery_payment_collections');
    }
};
