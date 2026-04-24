<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosPantau;
use App\Models\Spa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * List semua pos pantau aktif (versi ringkas).
     */
    public function index(Request $request): JsonResponse
    {
        $query = PosPantau::query();

        if ($request->boolean('aktif_only', true)) {
            $query->aktif();
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('kode')->get(),
        ]);
    }

    /**
     * List pos + stat tambahan untuk Admin Dashboard:
     *  - spa_today: jumlah SPA hari ini
     *  - last_activity_at: timestamp SPA terakhir (untuk status Online/Idle/Offline)
     *  - radius_meters alias (FE expect field name ini)
     */
    public function indexWithStats(): JsonResponse
    {
        $rows = PosPantau::query()
            ->orderBy('kode')
            ->get()
            ->map(function (PosPantau $p) {
                $spaToday = Spa::where('pos_pantau_id', $p->id)
                    ->whereDate('tanggal_spa', today())
                    ->count();

                $lastSpa = Spa::where('pos_pantau_id', $p->id)
                    ->orderByDesc('created_at')
                    ->value('created_at');

                return [
                    'id' => $p->id,
                    'kode' => $p->kode,
                    'nama' => $p->nama,
                    'kabupaten' => $p->kabupaten,
                    'alamat' => $p->alamat,
                    'latitude' => (float) $p->latitude,
                    'longitude' => (float) $p->longitude,
                    'radius_meter' => $p->radius_meter,
                    'radius_meters' => $p->radius_meter, // alias untuk FE
                    'status' => $p->status,
                    'spa_today' => $spaToday,
                    'last_activity_at' => $lastSpa,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $rows,
        ]);
    }

    /**
     * Detail pos pantau + jumlah SPA hari ini.
     */
    public function show(PosPantau $pos): JsonResponse
    {
        $pos->loadCount([
            'spaRecords as spa_hari_ini' => fn ($q) => $q->whereDate('tanggal_spa', today()),
            'spaRecords as spa_total',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $pos,
        ]);
    }
}
