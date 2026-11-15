<?php

use App\Http\Middleware\PastikanAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => PastikanAdmin::class,
        ]);

        // Pengguna yang belum login diarahkan ke halaman masuk (permintaan JSON mendapat 401)
        $middleware->redirectGuestsTo(fn () => route('masuk'));
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
