<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('market_payment_collections')) {
            Schema::create('market_payment_collections', function (Blueprint $table): void {
                $table->id();
                $table->string('payment_number', 60)->unique();
                $table->foreignId('market_stall_lease_id')->nullable()->constrained('market_stall_leases')->nullOnDelete();
                $table->foreignId('collection_dispatch_item_id')->nullable()->constrained('collection_dispatch_items')->nullOnDelete();
                $table->decimal('amount_paid', 14, 2)->default(0);
                $table->string('payer_name', 150)->nullable();
                $table->dateTime('payment_date')->nullable();
                $table->text('collector_note')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['market_stall_lease_id', 'payment_date'], 'idx_market_payment_lease_date');
            });
        }

        if (! Schema::hasTable('collection_dispatch_items')) {
            return;
        }

        if (Schema::hasColumn('collection_dispatch_items', 'fishport_log_id')) {
            $this->dropForeignKeysByColumn('collection_dispatch_items', 'fishport_log_id');
        }

        Schema::table('collection_dispatch_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('collection_dispatch_items', 'market_stall_lease_id')) {
                $table->foreignId('market_stall_lease_id')->nullable()->after('fishport_log_id')->constrained('market_stall_leases')->nullOnDelete();
            }
            if (! Schema::hasColumn('collection_dispatch_items', 'market_payment_collection_id')) {
                $table->foreignId('market_payment_collection_id')->nullable()->after('payment_record_id')->constrained('market_payment_collections')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('collection_dispatch_items', 'fishport_log_id')) {
            DB::statement('ALTER TABLE `collection_dispatch_items` MODIFY COLUMN `fishport_log_id` BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE `collection_dispatch_items` ADD CONSTRAINT `collection_dispatch_items_fishport_log_id_foreign` FOREIGN KEY (`fishport_log_id`) REFERENCES `fishport_logs`(`id`) ON DELETE CASCADE');
        }

        if ($this->indexExists('collection_dispatch_items', 'dispatch_market_lease_unique')) {
            Schema::table('collection_dispatch_items', function (Blueprint $table): void {
                $table->dropUnique('dispatch_market_lease_unique');
            });
        }

        Schema::table('collection_dispatch_items', function (Blueprint $table): void {
            if (! $this->indexExists('collection_dispatch_items', 'dispatch_market_lease_unique')) {
                $table->unique(['collection_dispatch_id', 'market_stall_lease_id'], 'dispatch_market_lease_unique');
            }
            if (! $this->indexExists('collection_dispatch_items', 'market_lease_status_idx')) {
                $table->index(['market_stall_lease_id', 'status'], 'market_lease_status_idx');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('collection_dispatch_items')) {
            if ($this->indexExists('collection_dispatch_items', 'market_lease_status_idx')) {
                Schema::table('collection_dispatch_items', function (Blueprint $table): void {
                    $table->dropIndex('market_lease_status_idx');
                });
            }

            if ($this->indexExists('collection_dispatch_items', 'dispatch_market_lease_unique')) {
                Schema::table('collection_dispatch_items', function (Blueprint $table): void {
                    $table->dropUnique('dispatch_market_lease_unique');
                });
            }

            Schema::table('collection_dispatch_items', function (Blueprint $table): void {
                if (Schema::hasColumn('collection_dispatch_items', 'market_payment_collection_id')) {
                    $table->dropConstrainedForeignId('market_payment_collection_id');
                }
                if (Schema::hasColumn('collection_dispatch_items', 'market_stall_lease_id')) {
                    $table->dropConstrainedForeignId('market_stall_lease_id');
                }
            });
        }

        if (Schema::hasTable('market_payment_collections')) {
            Schema::drop('market_payment_collections');
        }
    }

    private function dropForeignKeysByColumn(string $tableName, string $columnName): void
    {
        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$tableName, $columnName]
        );

        foreach ($foreignKeys as $foreignKey) {
            $constraintName = (string) ($foreignKey->CONSTRAINT_NAME ?? '');
            if ($constraintName === '') {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                str_replace('`', '``', $tableName),
                str_replace('`', '``', $constraintName)
            ));
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `' . str_replace('`', '``', $tableName) . '` WHERE Key_name = ?', [$indexName]);
        return count($rows) > 0;
    }
};

