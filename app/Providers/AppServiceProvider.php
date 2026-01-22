<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa HTTPS jika di Production (Agar Cloudflare Tunnel aman)
        // if($this->app->environment('production') || $this->app->environment('local')) {
        //     URL::forceScheme('https');
        // }
    }
}
