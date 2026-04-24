<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petani', function (Blueprint $table) {
            $table->id();
            $table->string('kode_petani', 20)->unique()->comment('Kode internal petani');
            $table->string('nama');
            $table->string('nik', 16)->nullable()->comment('NIK KTP petani');
            $table->string('no_wa', 20)->nullable()->comment('Nomor WhatsApp untuk notifikasi');
            $table->string('alamat')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kelompok_tani')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['nama', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petani');
    }
};
