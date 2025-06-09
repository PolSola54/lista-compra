<?php

namespace App\Providers;

use App\Services\FirebaseService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('firebase.database', function ($app) {
            return (new FirebaseService())->getDatabase();
        });
    }

    public function boot()
    {
        //
    }
}