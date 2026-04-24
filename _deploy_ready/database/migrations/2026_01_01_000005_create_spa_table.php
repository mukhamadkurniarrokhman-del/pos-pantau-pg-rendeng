<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spa', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spa', 32)->unique()->comment('Format: {KODE_POS}-{YYYYMMDD}-{URUTAN} — contoh JPH-20260422-012');
            $table->foreignId('pos_pantau_id')->constrained('pos_pantau');
            $table->date('tanggal_spa');
            $table->integer('urutan')->comment('Urutan truk per pos per hari (reset tiap hari)');

            // Data truk & sopir
            $table->string('nomor_polisi', 15);
            $table->string('nama_sopir');

            // Data dari kontrak (di-copy snapshot untuk historis)
            $table->foreignId('kontrak_id')->constrained('kontrak');
            $table->string('snapshot_nomor_kontrak', 40);
            $table->string('snapshot_nama_petani');
            $table->string('snapshot_nama_kebun');

            // GPS verification
            $table->decimal('gps_latitude', 10, 7);
            $table->decimal('gps_longitude', 10, 7);
            $table->decimal('gps_accuracy_meters', 8, 2)->nullable();
            $table->decimal('distance_to_pos_meters', 8, 2)->nullable();
            $table->boolean('gps_valid')->default(false);
            $table->boolean('is_mock_location')->default(false)->comment('Terdeteksi fake GPS?');
            $table->json('gps_metadata')->nullable()->comment('Device info, provider, dll');

            // Status & workflow
            $table->enum('status', [
                'draft',        // baru di-create, belum lengkap
                'verified',     // semua cek lolos, notif WA terkirim
                'pending',      // menunggu review
                'rejected',     // ditolak (fake GPS / data invalid)
            ])->default('draft');
            $table->text('rejection_reason')->nullable();

            // Audit
            $table->foreignId('petugas_id')->constrained('users');
            $table->timestamp('waktu_pemantauan')->comment('Waktu truk dipantau di pos');
            $table->timestamps();

            // Indexes untuk query cepat
            $table->index(['pos_pantau_id', 'tanggal_spa']);
            $table->index(['tanggal_spa', 'status']);
            $table->index('nomor_polisi');
            $table->index('waktu_pemantauan');
            $table->unique(['pos_pantau_id', 'tanggal_spa', 'urutan'], 'unique_urutan_per_pos_per_hari');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spa');
    }
};
