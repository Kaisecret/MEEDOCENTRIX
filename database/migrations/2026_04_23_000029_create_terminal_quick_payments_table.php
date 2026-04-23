<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('terminal_quick_payments')) {
            return;
        }

        Schema::create('terminal_quick_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payer_name', 160);
            $table->decimal('total_payment', 12, 2)->default(0);
            $table->dateTime('payment_date');
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('payment_date', 'idx_terminal_quick_payment_date');
            $table->index('payer_name', 'idx_terminal_quick_payment_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_quick_payments');
    }
};

