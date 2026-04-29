<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * List semua petugas dengan status online berdasarkan last_ping_at.
     * Dipakai Admin Dashboard untuk widget "Petugas Online".
     */
    public function active(): JsonResponse
    {
        $users = User::with('pos:id,kode,nama,kabupaten')
            ->where('role', 'petugas_pos')
            ->where('is_active', true)
            ->orderBy('nip')
            ->get(['id', 'nip', 'name', 'pos_pantau_id', 'last_login_at', 'last_ping_at']);

        $now = now();

        $payload = $users->map(function ($u) use ($now) {
            $lastPing = $u->last_ping_at;
            $secsAgo = $lastPing ? $now->diffInSeconds($lastPing) : null;

            $status = 'never';
            if ($secsAgo !== null) {
                if ($secsAgo <= 60)        $status = 'online';
                elseif ($secsAgo <= 300)   $status = 'idle';
                else                       $status = 'offline';
            }

            return [
                'id' => $u->id,
                'nip' => $u->nip,
                'name' => $u->name,
                'pos' => $u->pos ? [
                    'kode' => $u->pos->kode,
                    'nama' => $u->pos->nama,
                    'kabupaten' => $u->pos->kabupaten,
                ] : null,
                'last_login_at' => $u->last_login_at,
                'last_ping_at' => $lastPing,
                'seconds_since_ping' => $secsAgo,
                'status' => $status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'now' => $now->toIso8601String(),
                'online_count' => $payload->where('status', 'online')->count(),
                'idle_count' => $payload->where('status', 'idle')->count(),
                'total' => $payload->count(),
                'users' => $payload->values(),
            ],
        ]);
    }
}
