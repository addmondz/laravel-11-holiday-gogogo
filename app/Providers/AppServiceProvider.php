<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // set the X-Robots-Tag header, to disable search engine indexing
        \Illuminate\Support\Facades\Response::macro('noIndex', function ($response) {
            return $response->header('X-Robots-Tag', 'noindex, nofollow');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
