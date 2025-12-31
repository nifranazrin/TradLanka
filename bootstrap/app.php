<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\DeliveryPersonMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
             'admin' => App\Http\Middleware\AdminMiddleware::class,
             'seller' => App\Http\Middleware\SellerMiddleware::class,
             'customer' => App\Http\Middleware\CustomerMiddleware::class,
             'delivery' => App\Http\Middleware\DeliveryPersonMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

