<?php

namespace App\Services;

use App\Models\Spa;
use App\Models\WaLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service untuk kirim WhatsApp via Fonnte (https://fonnte.com).
 *
 * Endpoint: POST https://api.fonnte.com/send
 * Header:   Authorization: {device_token}
 * Body:     target (phone), message (text), countryCode (62)
 *
 * Target umum: petani (pemilik kontrak) notifikasi truk sudah lewat pos.
 */
class FonnteService
{
    public function __construct(
        protected ?string $token = null,
        protected ?string $url = null,
        protected string $countryCode = '62',
    ) {
        $this->token = $token ?? config('fonnte.token');
        $this->url = $url ?? config('fonnte.url', 'https://api.fonnte.com/send');
        $this->countryCode = config('fonnte.country_code', '62');
    }

    /**
     * Kirim pesan WA dan catat ke wa_log.
     *
     * @return WaLog  Log pengiriman (termasuk jika gagal)
     */
    public function send(string $targetPhone, string $message, ?Spa $spa = null, ?string $targetName = null): WaLog
    {
        $phone = $this->normalizePhone($targetPhone);

        $log = WaLog::create([
            'spa_id' => $spa?->id,
            'target_phone' => $phone,
            'target_name' => $targetName,
            'message' => $message,
            'status' => 'pending',
        ]);

        if (! $this->token) {
            Log::warning('Fonnte token belum dikonfigurasi, pesan tidak dikirim', [
                'wa_log_id' => $log->id,
            ]);

            $log->update([
                'status' => 'failed',
                'fonnte_response' => 'FONNTE_TOKEN belum dikonfigurasi',
            ]);

            return $log;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $this->token])
                ->asForm()
                ->timeout(15)
                ->post($this->url, [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => $this->countryCode,
                ]);

            $body = $response->json() ?? ['raw' => $response->body()];
            $ok = $response->successful() && ($body['status'] ?? false);

            $log->update([
                'status' => $ok ? 'sent' : 'failed',
                'fonnte_message_id' => $body['id'][0] ?? ($body['detail'] ?? null),
                'fonnte_response' => json_encode($body, JSON_UNESCAPED_UNICODE),
                'sent_at' => $ok ? now() : null,
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal kirim WA Fonnte', [
                'wa_log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'fonnte_response' => 'Exception: ' . $e->getMessage(),
                'retry_count' => $log->retry_count + 1,
            ]);
        }

        return $log->fresh();
    }

    /**
     * Retry WA log yang masih bisa di-retry.
     */
    public function retry(WaLog $log): WaLog
    {
        if (! $log->canRetry()) {
            return $log;
        }

        $log->increment('retry_count');

        return $this->send(
            $log->target_phone,
            $log->message,
            $log->spa,
            $log->target_name
        );
    }

    /**
     * Build pesan notifikasi truk sudah dipantau di pos.
     */
    public function buildNotifPetani(Spa $spa): string
    {
        $posNama = $spa->pos?->nama ?? 'Pos Pantau';
        $tanggal = $spa->waktu_pemantauan?->translatedFormat('d F Y H:i') ?? now()->translatedFormat('d F Y H:i');

        return <<<MSG
        *PG RENDENG - NOTIFIKASI PEMANTAUAN*

        Assalamualaikum Bapak/Ibu {$spa->snapshot_nama_petani},

        Truk pengangkut tebu dari kebun *{$spa->snapshot_nama_kebun}* telah terpantau di:

        Pos       : {$posNama}
        No. SPA   : {$spa->nomor_spa}
        Plat      : {$spa->nomor_polisi}
        Sopir     : {$spa->nama_sopir}
        Waktu     : {$tanggal}

        Truk akan menuju PG Rendeng untuk ditimbang. Mohon dipantau.

        _Pesan otomatis — tidak perlu dibalas._
        MSG;
    }

    /**
     * Normalisasi nomor telepon ke format internasional (62xxx).
     */
    public function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        if (str_starts_with($clean, '62')) {
            return $clean;
        }

        return $this->countryCode . $clean;
    }
}
