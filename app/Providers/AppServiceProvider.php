<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Set locale ke Bahasa Indonesia untuk translatedFormat()
        \Carbon\Carbon::setLocale('id');
    }
}
