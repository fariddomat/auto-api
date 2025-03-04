<?php

namespace Fariddomat\AutoApi;

use Illuminate\Support\ServiceProvider;

class AutoApiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Fariddomat\AutoApi\Commands\MakeAutoApi::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}