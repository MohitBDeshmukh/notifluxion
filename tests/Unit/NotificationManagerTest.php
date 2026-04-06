<?php

namespace Notifluxion\LaravelNotify\Tests\Unit;

use Notifluxion\LaravelNotify\Tests\TestCase;
use Notifluxion\LaravelNotify\Facades\Notify;
use Notifluxion\LaravelNotify\Manager\NotificationManager;
use Notifluxion\LaravelNotify\Contracts\DriverInterface;

class NotificationManagerTest extends TestCase
{
    public function test_manager_can_be_resolved_from_container()
    {
        $manager = $this->app->make('notify');
        $this->assertInstanceOf(NotificationManager::class, $manager);
    }

    public function test_facade_resolves_the_manager()
    {
        $this->assertEquals('1.2.0', Notify::version());
    }

    public function test_resolves_default_driver()
    {
        $manager = $this->app->make('notify');
        
        $driver = $manager->channel('smtp');
        
        $this->assertInstanceOf(DriverInterface::class, $driver);
    }
}
