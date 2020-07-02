<?php

namespace TulsaPublicSchools\SnipeItCustomizations\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use TulsaPublicSchools\SnipeItCustomizations\Console\Commands\SystemBackupRestore;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the provider services.
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SystemBackupRestore::class,
            ]);
        }
    }

    /**
     * Register the provider services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
    }
}
