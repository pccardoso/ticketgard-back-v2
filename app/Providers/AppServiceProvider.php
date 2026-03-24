<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OCRService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OCRService::class, OCRService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
