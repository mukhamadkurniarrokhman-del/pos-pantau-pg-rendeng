<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spa_id')->nullable()->constrained('spa')->nullOnDelete();
            $table->string('target_phone', 20);
            $table->string('target_name')->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'queued'])->default('pending');
            $table->string('fonnte_message_id')->nullable();
            $table->text('fonnte_response')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'spa_id']);
            $table->index('target_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_log');
    }
};
