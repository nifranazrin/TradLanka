<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use App\Models\Category; 

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // This shares '$globalCategories' with EVERY view file in your project
        View::composer('*', function ($view) {
            $view->with('globalCategories', Category::whereNull('parent_id')
                ->with('subcategories')
                ->get());
        });
    }
}