<?php

namespace LHD\Providers;

use Illuminate\Support\ServiceProvider;
use LHD\Commands\Deploy;
use LHD\Commands\AppFromEnv;

class Install extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Deploy::class,
                AppFromEnv::class,
            ]);
        }
    }
}
