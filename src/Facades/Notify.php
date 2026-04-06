<?php

namespace Notifluxion\LaravelNotify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Notifluxion\LaravelNotify\Contracts\DriverInterface channel(?string $name = null)
 * @method static void send(mixed $notifiable, mixed $notification)
 * @method static string version()
 *
 * @see \Notifluxion\LaravelNotify\Manager\NotificationManager
 */
class Notify extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'notify';
    }
}
