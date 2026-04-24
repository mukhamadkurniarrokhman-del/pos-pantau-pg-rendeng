<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fonnte WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk layanan Fonnte (https://fonnte.com).
    | Token didapatkan dari dashboard Fonnte setelah scan QR WhatsApp.
    |
    */

    'token' => env('FONNTE_TOKEN', ''),

    'url' => env('FONNTE_URL', 'https://api.fonnte.com/send'),

    'country_code' => env('FONNTE_COUNTRY_CODE', '62'),

    'default_options' => [
        'countryCode' => '62',
        'delay' => '2',
    ],

    'timeout' => 15,

    // Retry settings jika pengiriman gagal
    'retry_times' => 3,
    'retry_delay_seconds' => 5,
];
