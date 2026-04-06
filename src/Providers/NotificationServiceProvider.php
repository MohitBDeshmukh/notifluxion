<?php

namespace Notifluxion\LaravelNotify\Providers;

use Illuminate\Support\ServiceProvider;
use Notifluxion\LaravelNotify\Manager\NotificationManager;
use Notifluxion\LaravelNotify\Contracts\ManagerInterface;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/notify.php', 'notify'
        );

        $this->app->singleton(ManagerInterface::class, function ($app) {
            return new NotificationManager($app);
        });

        // Queue Strategies bindings
        $this->app->bind('notify.strategy.sync', \Notifluxion\LaravelNotify\Queue\Strategies\SyncQueueStrategy::class);
        $this->app->bind('notify.strategy.database', \Notifluxion\LaravelNotify\Queue\Strategies\DatabaseQueueStrategy::class);

        // Alias for the facade
        $this->app->bind('notify', function ($app) {
            return $app->make(ManagerInterface::class);
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/notify.php' => config_path('notify.php'),
            ], 'notify-config');

            $this->publishes([
                __DIR__.'/../../database/migrations/' => database_path('migrations'),
            ], 'notify-migrations');
            
            // Register CLI commands if any
            $this->commands([
                \Notifluxion\LaravelNotify\Console\Commands\InstallCommand::class,
                \Notifluxion\LaravelNotify\Console\Commands\UninstallCommand::class,
                \Notifluxion\LaravelNotify\Console\Commands\TestLiveProvidersCommand::class,
                \Notifluxion\LaravelNotify\Console\Commands\WorkCommand::class,
            ]);
        }
    }
}
