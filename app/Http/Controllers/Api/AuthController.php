<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login via email ATAU NIP + password.
     * Frontend admin pakai email, frontend petugas bisa pakai keduanya.
     * Return Sanctum token + profil + pos yang di-assign.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'nullable|string',   // boleh email ATAU NIP (nama field historis)
            'nip' => 'nullable|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $identifier = $data['email'] ?? $data['nip'] ?? null;
        if (! $identifier) {
            throw ValidationException::withMessages([
                'email' => 'Email atau NIP wajib diisi.',
            ]);
        }

        // Deteksi: kalau ada '@' → cari by email, selain itu → cari by NIP
        $isEmail = str_contains($identifier, '@');

        $user = User::with('pos')
            ->where($isEmail ? 'email' : 'nip', trim($identifier))
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email/NIP atau password salah, atau akun tidak aktif.',
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken($data['device_name'] ?? 'mobile-petugas')->plainTextToken;

        $posPayload = $user->pos ? [
            'id' => $user->pos->id,
            'kode' => $user->pos->kode,
            'nama' => $user->pos->nama,
            'kabupaten' => $user->pos->kabupaten,
            'latitude' => (float) $user->pos->latitude,
            'longitude' => (float) $user->pos->longitude,
            // Expose both key variants untuk kompatibilitas FE
            'radius_meter' => $user->pos->radius_meter,
            'radius_meters' => $user->pos->radius_meter,
        ] : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'nip' => $user->nip,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'last_login_at' => $user->last_login_at,
                    // FE petugas akses via user.pos_pantau, FE admin via user.pos → sediakan keduanya
                    'pos' => $posPayload,
                    'pos_pantau' => $posPayload,
                ],
            ],
        ]);
    }

    /**
     * Logout → revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Ambil profil petugas yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('pos');

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }
}
