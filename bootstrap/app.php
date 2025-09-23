<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'never.logout'      => \App\Http\Middleware\NeverLogout::class,
            'pin.lock'          => \App\Http\Middleware\PinLock::class,
            'check.user.status' => \App\Http\Middleware\CheckUserStatus::class,
        ]);
        // $middleware->append(\App\Http\Middleware\NeverLogout::class); // Optional global
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
