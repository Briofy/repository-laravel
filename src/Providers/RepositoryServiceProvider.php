<?php

namespace Briofy\RepositoryLaravel\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/briofy-repository.php', 'briofy-repository');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Config/briofy-repository.php' => config_path('briofy-repository.php'),
            ], 'briofy-repository-config');
        }
    }
}
