<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType; // <-- 1. IMPORT THE CORRECT STRINGTYPE CLASS
use Illuminate\Support\Facades\View;
use App\Models\Category;

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
        // 2. THIS IS THE CORRECTED LOGIC
        if (!Type::hasType('enum')) {
            Type::addType('enum', StringType::class); // Use the class, not the string 'string'
        }
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        // Your existing view composer for the navigation
        View::composer(['components.public-header', 'components.public-footer'], function ($view) {
            $view->with('navCategories', Category::orderBy('name')->take(6)->get());
        });
    }
}