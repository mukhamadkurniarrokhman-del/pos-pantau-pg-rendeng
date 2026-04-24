<?php

namespace App\Services;

use App\Models\PosPantau;

/**
 * Service untuk verifikasi GPS petugas di pos pantau.
 *
 * Tugas:
 * 1. Hitung jarak titik GPS ke lokasi pos (Haversine)
 * 2. Cek apakah dalam radius yang diizinkan
 * 3. Validasi akurasi GPS (tolak jika > threshold)
 * 4. Deteksi fake GPS via flag mock_location dari client
 * 5. Validasi metadata device (untuk audit trail)
 */
class GpsService
{
    /**
     * Verifikasi lokasi GPS submit petugas.
     *
     * @param  array{latitude:float, longitude:float, accuracy?:float, is_mock?:bool, metadata?:array}  $payload
     * @return array{valid:bool, distance:float, reason:?string, details:array}
     */
    public function verify(PosPantau $pos, array $payload): array
    {
        $lat = (float) $payload['latitude'];
        $lng = (float) $payload['longitude'];
        $accuracy = isset($payload['accuracy']) ? (float) $payload['accuracy'] : null;
        $isMock = (bool) ($payload['is_mock'] ?? false);

        $maxAccuracy = (float) config('services.gps.min_accuracy_meters', 20);
        $distance = $pos->distanceTo($lat, $lng);
        $withinRadius = $distance <= $pos->radius_meter;

        // Rule 1: Fake GPS → tolak absolute
        if ($isMock) {
            return [
                'valid' => false,
                'distance' => $distance,
                'reason' => 'Fake/mock GPS terdeteksi. Silakan matikan aplikasi fake GPS.',
                'details' => compact('lat', 'lng', 'accuracy', 'distance', 'withinRadius', 'isMock'),
            ];
        }

        // Rule 2: Akurasi GPS terlalu rendah → tolak
        if ($accuracy !== null && $accuracy > $maxAccuracy) {
            return [
                'valid' => false,
                'distance' => $distance,
                'reason' => "Akurasi GPS terlalu rendah ({$accuracy}m). Min. {$maxAccuracy}m. Pindah ke area terbuka.",
                'details' => compact('lat', 'lng', 'accuracy', 'distance', 'withinRadius'),
            ];
        }

        // Rule 3: Di luar radius pos → tolak
        if (! $withinRadius) {
            return [
                'valid' => false,
                'distance' => $distance,
                'reason' => sprintf(
                    'Posisi Anda %.0f meter dari pos %s (max %d m). Silakan mendekat.',
                    $distance,
                    $pos->nama,
                    $pos->radius_meter
                ),
                'details' => compact('lat', 'lng', 'accuracy', 'distance', 'withinRadius'),
            ];
        }

        return [
            'valid' => true,
            'distance' => $distance,
            'reason' => null,
            'details' => compact('lat', 'lng', 'accuracy', 'distance', 'withinRadius'),
        ];
    }
}
