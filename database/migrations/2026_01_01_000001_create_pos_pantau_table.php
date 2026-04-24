<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_pantau', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 3)->unique()->comment('JPR, PTI, RBG, JPH, TDN, GBG');
            $table->string('nama');
            $table->string('kabupaten');
            $table->string('alamat')->nullable();
            $table->decimal('latitude', 10, 7)->comment('Koordinat resmi pos untuk verifikasi GPS');
            $table->decimal('longitude', 10, 7);
            $table->integer('radius_meter')->default(50)->comment('Radius toleransi lokasi pos');
            $table->enum('status', ['aktif', 'offline', 'maintenance'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_pantau');
    }
};
