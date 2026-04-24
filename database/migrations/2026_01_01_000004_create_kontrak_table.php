<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kontrak', 40)->unique()->comment('Format: KTR-PGR-YYYY-NNNNN');
            $table->foreignId('petani_id')->constrained('petani');
            $table->foreignId('kebun_id')->constrained('kebun');
            $table->integer('musim_giling')->comment('Tahun musim giling, misal 2026');
            $table->date('tanggal_kontrak');
            $table->date('tanggal_mulai_panen')->nullable();
            $table->date('tanggal_akhir_panen')->nullable();
            $table->decimal('estimasi_tonase', 10, 2)->nullable()->comment('Target tonase kontrak (dicatat di pabrik, bukan pos)');
            $table->decimal('harga_per_ton', 12, 2)->nullable()->comment('Harga per ton tebu (Rp)');
            $table->enum('status', ['aktif', 'selesai', 'batal', 'pending'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['status', 'musim_giling']);
            $table->index('nomor_kontrak');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
