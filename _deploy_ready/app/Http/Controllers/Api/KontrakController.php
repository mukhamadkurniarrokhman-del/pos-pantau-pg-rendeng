<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint untuk lookup kontrak oleh petugas pos.
 *
 * Petugas hanya ketik nomor kontrak → sistem auto-return
 * nama petani, nama kebun, no WA. Tidak perlu ngetik ulang.
 */
class KontrakController extends Controller
{
    /**
     * Cari kontrak by nomor kontrak (exact match).
     * Dipakai petugas pos saat input SPA.
     */
    public function lookup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nomor_kontrak' => 'required|string|max:40',
        ]);

        $kontrak = Kontrak::with(['petani:id,kode_petani,nama,no_wa', 'kebun:id,kode_kebun,nama,nomor_blok'])
            ->where('nomor_kontrak', trim($data['nomor_kontrak']))
            ->first();

        if (! $kontrak) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Nomor kontrak tidak ditemukan di database.',
            ], 404);
        }

        if (! $kontrak->isAktif()) {
            return response()->json([
                'status' => 'inactive',
                'message' => 'Kontrak ini tidak aktif (status: ' . $kontrak->status . ').',
                'data' => $kontrak,
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $kontrak->id,
                'nomor_kontrak' => $kontrak->nomor_kontrak,
                'musim_giling' => $kontrak->musim_giling,
                'estimasi_tonase' => $kontrak->estimasi_tonase,
                'status' => $kontrak->status,
                'petani' => [
                    'nama' => $kontrak->petani->nama ?? '-',
                    'no_wa' => $kontrak->petani->no_wa ?? null,
                ],
                'kebun' => [
                    'nama' => $kontrak->kebun->nama ?? '-',
                    'nomor_blok' => $kontrak->kebun->nomor_blok ?? null,
                ],
            ],
        ]);
    }

    /**
     * Alias versi REST: GET /api/kontrak/{nomor}
     * Dipakai oleh Petugas_App.html.
     */
    public function showByNomor(string $nomor): JsonResponse
    {
        $kontrak = Kontrak::with(['petani:id,kode_petani,nama,no_wa', 'kebun:id,kode_kebun,nama,nomor_blok'])
            ->where('nomor_kontrak', trim($nomor))
            ->first();

        if (! $kontrak) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Nomor kontrak tidak ditemukan di database.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $kontrak->id,
                'nomor_kontrak' => $kontrak->nomor_kontrak,
                'musim_giling' => $kontrak->musim_giling,
                'estimasi_tonase' => $kontrak->estimasi_tonase,
                'status' => $kontrak->status,
                'petani' => [
                    'nama' => $kontrak->petani->nama ?? '-',
                    'no_wa' => $kontrak->petani->no_wa ?? null,
                ],
                'kebun' => [
                    'nama' => $kontrak->kebun->nama ?? '-',
                    'nomor_blok' => $kontrak->kebun->nomor_blok ?? null,
                ],
            ],
        ]);
    }

    /**
     * Search kontrak by nomor/nama petani untuk dropdown/autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        if (strlen($q) < 3) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $hasil = Kontrak::with(['petani:id,nama', 'kebun:id,nama'])
            ->aktif()
            ->where(function ($query) use ($q) {
                $query->where('nomor_kontrak', 'like', "%$q%")
                    ->orWhereHas('petani', fn ($p) => $p->where('nama', 'like', "%$q%"));
            })
            ->limit(10)
            ->get(['id', 'nomor_kontrak', 'petani_id', 'kebun_id', 'musim_giling']);

        return response()->json(['status' => 'success', 'data' => $hasil]);
    }
}
