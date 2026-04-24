<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kebun', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kebun', 20)->unique();
            $table->string('nama');
            $table->foreignId('petani_id')->constrained('petani')->cascadeOnDelete();
            $table->decimal('luas_hektar', 8, 2)->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('nomor_blok', 10)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['nama', 'petani_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kebun');
    }
};
