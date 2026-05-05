<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32)->default('info');
            $table->string('title', 160);
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('event_key', 191)->nullable();
            $table->json('payload')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at'], 'app_notifications_user_read_idx');
            $table->unique(['user_id', 'event_key'], 'app_notifications_user_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
