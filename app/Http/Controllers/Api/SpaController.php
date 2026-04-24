<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use App\Models\PosPantau;
use App\Models\Spa;
use App\Services\FonnteService;
use App\Services\GpsService;
use App\Services\PhotoService;
use App\Services\SpaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SpaController extends Controller
{
    public function __construct(
        protected SpaService $spaService,
        protected GpsService $gpsService,
        protected PhotoService $photoService,
        protected FonnteService $fonnteService,
    ) {}

    /**
     * List SPA (filter: pos, tanggal, status).
     * Admin lihat semua, petugas hanya milik pos-nya.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Spa::with(['pos:id,kode,nama', 'petugas:id,name', 'fotoMuatan:id,spa_id,url_thumbnail,jenis'])
            ->orderByDesc('waktu_pemantauan');

        // Role-based filter
        if ($user->isPetugas()) {
            $query->where('pos_pantau_id', $user->pos_pantau_id);
        }

        if ($request->filled('pos_id')) {
            $query->where('pos_pantau_id', $request->pos_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_spa', $request->tanggal);
        }

        // Filter rentang tanggal (untuk Arsip/Riwayat Lengkap)
        if ($request->filled('tanggal_from')) {
            $query->whereDate('tanggal_spa', '>=', $request->tanggal_from);
        }
        if ($request->filled('tanggal_to')) {
            $query->whereDate('tanggal_spa', '<=', $request->tanggal_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('nomor_spa', 'like', "%$q%")
                    ->orWhere('nomor_polisi', 'like', "%$q%")
                    ->orWhere('nama_sopir', 'like', "%$q%")
                    ->orWhere('snapshot_nama_petani', 'like', "%$q%");
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($request->integer('per_page', 20)),
        ]);
    }

    /**
     * Detail SPA lengkap (termasuk foto & WA log).
     */
    public function show(Spa $spa): JsonResponse
    {
        $spa->load(['pos', 'petugas:id,name,nip', 'kontrak', 'fotoMuatan', 'waLog']);

        return response()->json(['status' => 'success', 'data' => $spa]);
    }

    /**
     * Create SPA baru:
     *  1. Validasi input
     *  2. Lookup kontrak → ambil snapshot petani/kebun/wa
     *  3. Verifikasi GPS (anti-fake, radius)
     *  4. Generate nomor SPA atomic
     *  5. Simpan transaksional
     *  6. Upload foto (jika ada)
     *  7. Trigger notif WA ke petani
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->pos_pantau_id) {
            throw ValidationException::withMessages([
                'pos_pantau_id' => 'Akun Anda belum di-assign ke pos pantau manapun.',
            ]);
        }

        // FormData browser kirim boolean sbg "0"/"1" — rule 'boolean' menerima keduanya,
        // lalu konversi eksplisit via FILTER_VALIDATE_BOOLEAN supaya aman di DB & di service.
        $data = $request->validate([
            'nomor_polisi' => 'required|string|max:15',
            'nama_sopir' => 'required|string|max:100',
            'nomor_kontrak' => 'required|string|max:40',
            'gps_latitude' => 'required|numeric|between:-90,90',
            'gps_longitude' => 'required|numeric|between:-180,180',
            'gps_accuracy_meters' => 'nullable|numeric|min:0',
            'is_mock_location' => 'nullable|boolean',
            'gps_metadata' => 'nullable',
            'waktu_pemantauan' => 'nullable|date',
            'foto_muatan.*' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
        ]);
        $data['is_mock_location'] = filter_var($data['is_mock_location'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $pos = PosPantau::findOrFail($user->pos_pantau_id);

        // Step 1: Lookup kontrak aktif
        $kontrak = Kontrak::with('petani', 'kebun')
            ->where('nomor_kontrak', $data['nomor_kontrak'])
            ->first();

        if (! $kontrak) {
            throw ValidationException::withMessages([
                'nomor_kontrak' => 'Nomor kontrak tidak ditemukan di database.',
            ]);
        }

        if (! $kontrak->isAktif()) {
            throw ValidationException::withMessages([
                'nomor_kontrak' => "Kontrak tidak aktif (status: {$kontrak->status}).",
            ]);
        }

        // Step 2: Cek duplikasi plat hari ini
        if ($this->spaService->isDuplicatePlateToday($pos, $data['nomor_polisi'])) {
            throw ValidationException::withMessages([
                'nomor_polisi' => 'Plat nomor ini sudah terdaftar hari ini di pos ini.',
            ]);
        }

        // Step 3: Verifikasi GPS
        $gpsCheck = $this->gpsService->verify($pos, [
            'latitude' => $data['gps_latitude'],
            'longitude' => $data['gps_longitude'],
            'accuracy' => $data['gps_accuracy_meters'] ?? null,
            'is_mock' => $data['is_mock_location'] ?? false,
            'metadata' => $data['gps_metadata'] ?? [],
        ]);

        // Step 4: Transaksi — generate SPA + simpan
        $spa = DB::transaction(function () use ($data, $pos, $kontrak, $gpsCheck, $user) {
            $gen = $this->spaService->generateNomor($pos);

            return Spa::create([
                'nomor_spa' => $gen['nomor_spa'],
                'pos_pantau_id' => $pos->id,
                'tanggal_spa' => $gen['tanggal'],
                'urutan' => $gen['urutan'],
                'nomor_polisi' => $this->spaService->normalizePlate($data['nomor_polisi']),
                'nama_sopir' => $data['nama_sopir'],
                'kontrak_id' => $kontrak->id,
                'snapshot_nomor_kontrak' => $kontrak->nomor_kontrak,
                'snapshot_nama_petani' => $kontrak->petani->nama ?? '-',
                'snapshot_nama_kebun' => $kontrak->kebun->nama ?? '-',
                'gps_latitude' => $data['gps_latitude'],
                'gps_longitude' => $data['gps_longitude'],
                'gps_accuracy_meters' => $data['gps_accuracy_meters'] ?? null,
                'distance_to_pos_meters' => $gpsCheck['distance'],
                'gps_valid' => $gpsCheck['valid'],
                'is_mock_location' => $data['is_mock_location'] ?? false,
                'gps_metadata' => $data['gps_metadata'] ?? null,
                'status' => $gpsCheck['valid'] ? 'verified' : 'rejected',
                'rejection_reason' => $gpsCheck['reason'],
                'petugas_id' => $user->id,
                'waktu_pemantauan' => $data['waktu_pemantauan'] ?? now(),
            ]);
        });

        // Step 5: Upload foto (jika ada)
        if ($request->hasFile('foto_muatan')) {
            foreach ((array) $request->file('foto_muatan') as $idx => $file) {
                $this->photoService->upload($spa, $file, $idx === 0 ? 'depan' : 'lainnya');
            }
        }

        // Step 6: Trigger WA jika verified & nomor petani ada
        $waLog = null;
        if ($spa->isVerified() && $kontrak->petani?->no_wa) {
            $waLog = $this->fonnteService->send(
                $kontrak->petani->no_wa,
                $this->fonnteService->buildNotifPetani($spa),
                $spa,
                $kontrak->petani->nama
            );
        }

        $spa->load('fotoMuatan', 'pos:id,kode,nama');

        return response()->json([
            'status' => $spa->isVerified() ? 'success' : 'rejected',
            'message' => $spa->isVerified()
                ? "SPA {$spa->nomor_spa} berhasil dibuat & notifikasi WA dikirim."
                : "SPA ditolak: {$spa->rejection_reason}",
            'data' => [
                'spa' => $spa,
                'wa_log' => $waLog,
            ],
        ], $spa->isVerified() ? 201 : 422);
    }

    /**
     * Upload foto tambahan untuk SPA yang sudah ada.
     */
    public function uploadFoto(Request $request, Spa $spa): JsonResponse
    {
        $data = $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:8192',
            'jenis' => 'nullable|in:depan,samping,atas,plat_nomor,lainnya',
        ]);

        $foto = $this->photoService->upload($spa, $data['foto'], $data['jenis'] ?? 'lainnya');

        return response()->json([
            'status' => 'success',
            'message' => 'Foto berhasil diupload',
            'data' => $foto,
        ], 201);
    }

    /**
     * Retry kirim WA untuk SPA (jika sebelumnya gagal).
     */
    public function retryWa(Spa $spa): JsonResponse
    {
        $log = $spa->waLog()->latest()->first();

        if (! $log) {
            // Belum pernah kirim, coba kirim fresh
            $kontrak = $spa->kontrak()->with('petani')->first();
            if (! $kontrak?->petani?->no_wa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nomor WA petani tidak tersedia.',
                ], 422);
            }

            $log = $this->fonnteService->send(
                $kontrak->petani->no_wa,
                $this->fonnteService->buildNotifPetani($spa),
                $spa,
                $kontrak->petani->nama
            );
        } else {
            $log = $this->fonnteService->retry($log);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Status WA: {$log->status}",
            'data' => $log,
        ]);
    }
}
