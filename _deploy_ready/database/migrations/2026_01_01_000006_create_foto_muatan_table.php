<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_muatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spa_id')->constrained('spa')->cascadeOnDelete();
            $table->enum('jenis', ['depan', 'samping', 'atas', 'plat_nomor', 'lainnya'])->default('lainnya');
            $table->string('path')->comment('Path file di storage');
            $table->string('url_thumbnail')->nullable();
            $table->integer('size_kb')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('hash_sha256', 64)->nullable()->comment('Hash untuk deteksi foto duplikat');
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->index(['spa_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_muatan');
    }
};
