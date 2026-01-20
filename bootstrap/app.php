<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\DeliveryPersonMiddleware;
use Illuminate\Http\Request;

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

        $middleware->redirectGuestsTo(function (Request $request) {
            // Check if the URL is for staff, admin, seller, or delivery routes
            if ($request->is('admin/*') || $request->is('seller/*') || $request->is('delivery/*')) {
                return route('staff.login'); // Redirect operational staff here
            }

            // Default for customers (using your popup login route)
            return route('login.popup');
        });
   })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

