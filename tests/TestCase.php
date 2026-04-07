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

        // Only enforce our safe SQLite in-memory fallback if the developer hasn't explicitly supplied an environment driver!
        // This flawlessly allows SqlServer, PGSQL, and MySQL environments to pass through natively when specified.
        if (!env('DB_CONNECTION') && !isset($_ENV['DB_CONNECTION'])) {
            $app['config']->set('database.default', 'testing');
            $app['config']->set('database.connections.testing', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        }
    }
}
