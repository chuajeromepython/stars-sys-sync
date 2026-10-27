<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('ecdc-generation', fn (Request $request): Limit => Limit::perMinute(3)->by('ecdc-generation:'.$request->user()->id));
        RateLimiter::for('ecdc-status', fn (Request $request): Limit => Limit::perMinute(60)->by('ecdc-status:'.$request->user()->id));
    }
}
