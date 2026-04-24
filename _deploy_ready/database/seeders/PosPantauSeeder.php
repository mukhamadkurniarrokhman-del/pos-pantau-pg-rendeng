<?php

namespace Database\Seeders;

use App\Models\PosPantau;
use Illuminate\Database\Seeder;

/**
 * 6 pos pantau PG Rendeng — batas wilayah Kudus dengan kabupaten sekitarnya.
 * Koordinat adalah perkiraan titik jalan utama; silakan disesuaikan dengan
 * lokasi fisik sebenarnya setelah survei lapangan.
 */
class PosPantauSeeder extends Seeder
{
    public function run(): void
    {
        $pos = [
            [
                'kode' => 'JPR',
                'nama' => 'Pos Pantau Jepara',
                'kabupaten' => 'Jepara',
                'alamat' => 'Perbatasan Kudus - Jepara, Jl. Raya Jepara KM 12',
                'latitude' => -6.7583300,
                'longitude' => 110.7550000,
                'radius_meter' => 50,
                'status' => 'aktif',
                'keterangan' => 'Mengawasi truk dari wilayah Jepara (Welahan, Mayong).',
            ],
            [
                'kode' => 'PTI',
                'nama' => 'Pos Pantau Pati',
                'kabupaten' => 'Pati',
                'alamat' => 'Perbatasan Kudus - Pati, Jl. Raya Pati KM 8',
                'latitude' => -6.7420000,
                'longitude' => 110.9080000,
                'radius_meter' => 50,
                'status' => 'aktif',
                'keterangan' => 'Jalur utama dari Pati barat & Juwana.',
            ],
            [
                'kode' => 'RBG',
                'nama' => 'Pos Pantau Rembang',
                'kabupaten' => 'Rembang',
                'alamat' => 'Perbatasan Pati - Rembang (transit), Pantura KM 110',
                'latitude' => -6.7080000,
                'longitude' => 111.3420000,
                'radius_meter' => 75,
                'status' => 'aktif',
                'keterangan' => 'Transit truk dari wilayah timur (Rembang, Lasem).',
            ],
            [
                'kode' => 'JPH',
                'nama' => 'Pos Pantau Japah',
                'kabupaten' => 'Blora',
                'alamat' => 'Kec. Japah, Kabupaten Blora',
                'latitude' => -7.0580000,
                'longitude' => 111.3050000,
                'radius_meter' => 50,
                'status' => 'aktif',
                'keterangan' => 'Jalur dari Blora barat.',
            ],
            [
                'kode' => 'TDN',
                'nama' => 'Pos Pantau Todanan',
                'kabupaten' => 'Blora',
                'alamat' => 'Kec. Todanan, Kabupaten Blora',
                'latitude' => -7.0310000,
                'longitude' => 111.2140000,
                'radius_meter' => 50,
                'status' => 'aktif',
                'keterangan' => 'Jalur penghubung Blora - Kudus via Cepu.',
            ],
            [
                'kode' => 'GBG',
                'nama' => 'Pos Pantau Grobogan',
                'kabupaten' => 'Grobogan',
                'alamat' => 'Perbatasan Kudus - Grobogan (Purwodadi)',
                'latitude' => -6.9150000,
                'longitude' => 110.8250000,
                'radius_meter' => 50,
                'status' => 'aktif',
                'keterangan' => 'Jalur selatan dari Purwodadi & Klambu.',
            ],
        ];

        foreach ($pos as $p) {
            PosPantau::updateOrCreate(['kode' => $p['kode']], $p);
        }
    }
}
