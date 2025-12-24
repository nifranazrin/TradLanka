<?php

return [

     //Authentication Defaults
   

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users', // Changed default to users (Customers)
    ],

     //Authentication Guards
    

    'guards' => [
        // 1. CUSTOMER GUARD 
        'web' => [
            'driver' => 'session',
            'provider' => 'users', 
        ],

        // 2. STAFF GUARD (General)
        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],

        // Seller guard (Points to staff table)
        'seller' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],

        // Admin guard (Points to staff table)
        'admin' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],

        // Delivery guard (Points to staff table)
        'delivery' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
    ],

    //User Providers
   

    'providers' => [
        
        // ADDED THIS: Provider for Customers
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class, // Points to your User model
        ],

        // Existing Provider for Staff
        'staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class, // Points to your Staff model
        ],
    ],

     //Resetting Passwords
    

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'staff' => [
            'provider' => 'staff',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];