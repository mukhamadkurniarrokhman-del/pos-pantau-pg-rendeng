<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosPantau;
use App\Models\Spa;
use App\Models\WaLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Endpoint dashboard untuk admin PG Rendeng.
 * KPI utama, grafik harian, ranking pos, alert fake GPS.
 */
class DashboardController extends Controller
{
    /**
     * Ringkasan KPI (total truk hari ini, verified, rejected, WA terkirim).
     */
    public function summary(Request $request): JsonResponse
    {
        $tanggal = $request->input('tanggal')
            ? Carbon::parse($request->input('tanggal'))
            : Carbon::today();

        $spaBase = Spa::whereDate('tanggal_spa', $tanggal);

        return response()->json([
            'status' => 'success',
            'data' => [
                'tanggal' => $tanggal->toDateString(),
                'total_truk' => (clone $spaBase)->count(),
                'verified' => (clone $spaBase)->where('status', 'verified')->count(),
                'rejected' => (clone $spaBase)->where('status', 'rejected')->count(),
                'pending' => (clone $spaBase)->where('status', 'pending')->count(),
                'fake_gps_terdeteksi' => (clone $spaBase)->where('is_mock_location', true)->count(),
                'wa_terkirim' => WaLog::whereDate('created_at', $tanggal)
                    ->whereIn('status', ['sent', 'delivered'])->count(),
                'wa_gagal' => WaLog::whereDate('created_at', $tanggal)
                    ->where('status', 'failed')->count(),
            ],
        ]);
    }

    /**
     * Breakdown per pos untuk tanggal tertentu (bar chart / doughnut).
     * FE admin expect field: kode, total, verified, rejected.
     */
    public function perPos(Request $request): JsonResponse
    {
        $tanggal = $request->input('tanggal')
            ? Carbon::parse($request->input('tanggal'))
            : Carbon::today();

        $rows = PosPantau::query()
            ->leftJoin('spa', function ($join) use ($tanggal) {
                $join->on('spa.pos_pantau_id', '=', 'pos_pantau.id')
                    ->whereDate('spa.tanggal_spa', $tanggal);
            })
            ->selectRaw("
                pos_pantau.id,
                pos_pantau.kode,
                pos_pantau.nama,
                pos_pantau.kabupaten,
                COUNT(spa.id) as total,
                COUNT(spa.id) as total_truk,
                SUM(CASE WHEN spa.status = 'verified' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN spa.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN spa.is_mock_location = true THEN 1 ELSE 0 END) as fake_gps
            ")
            ->groupBy('pos_pantau.id', 'pos_pantau.kode', 'pos_pantau.nama', 'pos_pantau.kabupaten')
            ->orderBy('pos_pantau.kode')
            ->get();

        return response()->json(['status' => 'success', 'data' => $rows]);
    }

    /**
     * Trend 7 hari terakhir (bar chart).
     * FE admin expect: tanggal, verified, rejected.
     */
    public function trend7Hari(): JsonResponse
    {
        $start = Carbon::today()->subDays(6);

        $rows = Spa::query()
            ->selectRaw("
                DATE(tanggal_spa) as tgl,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")
            ->where('tanggal_spa', '>=', $start)
            ->groupByRaw('DATE(tanggal_spa)')
            ->orderBy('tgl')
            ->get();

        $data = collect();
        for ($i = 0; $i < 7; $i++) {
            $tgl = $start->copy()->addDays($i)->toDateString();
            $row = $rows->firstWhere('tgl', $tgl);
            $data->push([
                'tanggal' => $tgl,
                'total' => (int) ($row->total ?? 0),
                'verified' => (int) ($row->verified ?? 0),
                'rejected' => (int) ($row->rejected ?? 0),
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Alert: SPA yang ditolak / fake GPS belakangan ini.
     */
    public function alerts(Request $request): JsonResponse
    {
        $alerts = Spa::with('pos:id,kode,nama', 'petugas:id,name')
            ->where(function ($q) {
                $q->where('is_mock_location', true)
                    ->orWhere('status', 'rejected');
            })
            ->whereDate('created_at', '>=', now()->subDays(3))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['status' => 'success', 'data' => $alerts]);
    }