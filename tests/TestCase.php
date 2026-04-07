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

    protected function defineEnvironment($app)
    {
        // Set configuration defaults for testing
        $app['config']->set('notify.default.email', 'smtp');
        
        // Force SQLite in-memory database to strictly prevent MySQL 'forge' connection refused errors 
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
