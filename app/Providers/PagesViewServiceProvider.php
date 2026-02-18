<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PagesViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::addNamespace('pages', resource_path('views/pages'));
    }
}