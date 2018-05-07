<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //Set timezone Carbon
        Carbon::setLocale('pt');

        // handle dingo's findOrFail problem
        app('api.exception')->register(function (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            abort(404);
        });

        if ($this->app->environment() == 'local') {
            $this->app->register('Wn\Generators\CommandsServiceProvider');
        }
    }
}
