<?php

/*
 * CORS configuration untuk mengizinkan akses dari frontend
 * (mobile app, dashboard React, atau file HTML lokal).
 */

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    // Kosongkan untuk environment development — di production,
    // ganti dengan domain frontend yang spesifik.
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),
];
