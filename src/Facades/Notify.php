<?php

namespace Notifluxion\LaravelNotify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Notifluxion\LaravelNotify\Contracts\DriverInterface channel(?string $name = null)
 * @method static void send(mixed $notifiables, mixed $notification, \DateTimeInterface|array|\Notifluxion\LaravelNotify\Scheduling\ScheduleBuilder|null $scheduleAt = null)
 * @method static int cancelTag(string $tag)
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
