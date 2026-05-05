<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('market_due_logs')) {
            return;
        }

        Schema::create('market_due_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('market_stall_lease_id')->constrained('market_stall_leases')->cascadeOnDelete();
            $table->date('due_date')->index();
            $table->string('billing_period', 20)->default('monthly');
            $table->unsignedInteger('billing_cycles')->default(1);
            $table->decimal('expected_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('due')->index();
            $table->foreignId('collection_dispatch_item_id')->nullable()->constrained('collection_dispatch_items')->nullOnDelete();
            $table->foreignId('market_payment_collection_id')->nullable()->constrained('market_payment_collections')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['market_stall_lease_id', 'due_date'], 'lease_due_unique');
            $table->index(['status', 'due_date'], 'market_due_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_due_logs');
    }
};

