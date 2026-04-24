<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KontrakController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\SpaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Pos Pantau PG Rendeng
|--------------------------------------------------------------------------
| Prefix: /api
| Auth  : Laravel Sanctum (Bearer token)
*/

/* ─── PUBLIC ─── */
Route::post('auth/login', [AuthController::class, 'login']);

Route::get('health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'pos-pantau-pg-rendeng',
    'time' => now()->toIso8601String(),
]));

/* ─── AUTHENTICATED ─── */
Route::middleware('auth:sanctum')->group(function () {

    // Auth profile
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Reference data — pos pantau
    Route::get('pos', [PosController::class, 'index']);
    Route::get('pos-pantau', [PosController::class, 'indexWithStats']); // FE admin dashboard
    Route::get('pos/{pos}', [PosController::class, 'show']);

    // Kontrak lookup (dipakai petugas waktu input SPA)
    Route::post('kontrak/lookup', [KontrakController::class, 'lookup']);
    Route::get('kontrak/search', [KontrakController::class, 'search']);
    Route::get('kontrak/{nomor}', [KontrakController::class, 'showByNomor']); // FE petugas: GET /api/kontrak/KTR-...

    // SPA (truk yang dipantau)
    Route::get('spa', [SpaController::class, 'index']);
    Route::post('spa', [SpaController::class, 'store']);
    Route::get('spa/{spa}', [SpaController::class, 'show']);
    Route::post('spa/{spa}/foto', [SpaController::class, 'uploadFoto']);
    Route::post('spa/{spa}/retry-wa', [SpaController::class, 'retryWa']);

    // Stats (FE admin dashboard: /api/stats/7d & /api/stats/per-pos)
    Route::prefix('stats')->middleware('admin')->group(function () {
        Route::get('7d', [DashboardController::class, 'trend7Hari']);
        Route::get('per-pos', [DashboardController::class, 'perPos']);
        Route::get('summary', [DashboardController::class, 'summary']);
        Route::get('alerts', [DashboardController::class, 'alerts']);
    });

    // Dashboard (alias lama, tetap dipertahankan supaya tidak breaking)
    Route::prefix('dashboard')->middleware('admin')->group(function () {
        Route::get('summary', [DashboardController::class, 'summary']);
        Route::get('per-pos', [DashboardController::class, 'perPos']);
        Route::get('trend-7-hari', [DashboardController::class, 'trend7Hari']);
        Route::get('alerts', [DashboardController::class, 'alerts']);
    });
});
