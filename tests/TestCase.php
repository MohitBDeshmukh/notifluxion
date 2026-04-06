<?php

namespace Notifluxion\LaravelNotify\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Notifluxion\LaravelNotify\Providers\NotificationServiceProvider;
use Notifluxion\LaravelNotify\Facades\Notify;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            NotificationServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Notify' => Notify::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function defineEnvironment($app)
    {
        // Set configuration defaults for testing
        $app['config']->set('notify.default.email', 'smtp');
    }
}
