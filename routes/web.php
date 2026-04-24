<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'Sistem Pos Pantau PG Rendeng',
        'status' => 'running',
        'docs' => 'Lihat README.md untuk dokumentasi API',
        'api_base' => url('/api'),
    ]);
});
