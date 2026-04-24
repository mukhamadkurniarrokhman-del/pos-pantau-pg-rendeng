<?php

namespace App\Services;

use App\Models\PosPantau;
use App\Models\Spa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Service untuk generate nomor SPA secara atomic.
 *
 * Format: {KODE_POS}-{YYYYMMDD}-{URUTAN3DIGIT}
 * Contoh: JPH-20260422-012
 *
 * Urutan di-reset tiap hari per pos, di-increment secara atomic
 * menggunakan row locking agar tidak terjadi race condition
 * saat beberapa petugas submit bersamaan.
 */
class SpaService
{
    /**
     * Generate nomor SPA baru dengan urutan atomic increment.
     *
     * @return array{nomor_spa: string, urutan: int, tanggal: Carbon}
     */
    public function generateNomor(PosPantau $pos, ?Carbon $tanggal = null): array
    {
        $tanggal = $tanggal ?? Carbon::today();

        return DB::transaction(function () use ($pos, $tanggal) {
            // Lock row terakhir pada kombinasi pos + tanggal untuk atomic increment
            $lastUrutan = Spa::query()
                ->where('pos_pantau_id', $pos->id)
                ->whereDate('tanggal_spa', $tanggal)
                ->lockForUpdate()
                ->max('urutan') ?? 0;

            $urutanBaru = $lastUrutan + 1;

            $nomorSpa = sprintf(
                '%s-%s-%03d',
                strtoupper($pos->kode),
                $tanggal->format('Ymd'),
                $urutanBaru
            );

            return [
                'nomor_spa' => $nomorSpa,
                'urutan' => $urutanBaru,
                'tanggal' => $tanggal,
            ];
        });
    }

    /**
     * Cek apakah nomor polisi sudah pernah masuk hari ini di pos yang sama.
     * Berguna untuk deteksi truk yang mencoba mendaftar ulang.
     */
    public function isDuplicatePlateToday(PosPantau $pos, string $nomorPolisi, ?Carbon $tanggal = null): bool
    {
        $tanggal = $tanggal ?? Carbon::today();
        $normalized = $this->normalizePlate($nomorPolisi);

        return Spa::query()
            ->where('pos_pantau_id', $pos->id)
            ->whereDate('tanggal_spa', $tanggal)
            ->whereRaw('UPPER(REPLACE(nomor_polisi, " ", "")) = ?', [$normalized])
            ->whereIn('status', ['verified', 'pending'])
            ->exists();
    }

    /**
     * Normalisasi plat nomor: uppercase + hilangkan spasi.
     */
    public function normalizePlate(string $plate): string
    {
        return strtoupper(preg_replace('/\s+/', '', $plate));
    }
}
