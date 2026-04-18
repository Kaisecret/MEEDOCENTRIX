<?php

use Illuminate\Database\Migrations\Migration;
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

        DB::statement(
            'ALTER TABLE cemetery_transactions MODIFY transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cemetery_transactions')) {
            return;
        }

        DB::statement(
            'ALTER TABLE cemetery_transactions MODIFY transaction_date DATE NOT NULL'
        );
    }
};

