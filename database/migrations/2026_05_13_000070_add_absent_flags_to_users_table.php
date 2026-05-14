<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'is_absent')) {
                $table->boolean('is_absent')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('users', 'absent_set_at')) {
                $table->timestamp('absent_set_at')->nullable()->after('is_absent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropColumns = [];
            if (Schema::hasColumn('users', 'absent_set_at')) {
                $dropColumns[] = 'absent_set_at';
            }
            if (Schema::hasColumn('users', 'is_absent')) {
                $dropColumns[] = 'is_absent';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

