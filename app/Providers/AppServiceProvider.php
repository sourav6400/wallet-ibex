<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
        // Force HTTPS if the request is behind a proxy (like ngrok) with HTTPS
        $request = $this->app->make(Request::class);
        if ($request->header('X-Forwarded-Proto') === 'https' || $request->secure()) {
            URL::forceScheme('https');
            $request->server->set('HTTPS', 'on');
        }
        // if (config('app.env') == 'production') {
        //     URL::forceScheme('https');
        //     $this->app['request']->server->set('HTTPS', 'on');
        // }
    }
}
