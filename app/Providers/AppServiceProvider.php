<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        /**
         * NOTE: For heroku production
         * heroku app uses http scheme in route() when app opens at https
         * so forms with csrf always fail
         */
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
