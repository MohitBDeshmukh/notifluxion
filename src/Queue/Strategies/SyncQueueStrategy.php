<?php

namespace Notifluxion\LaravelNotify\Queue\Strategies;

use Notifluxion\LaravelNotify\Contracts\QueueStrategyInterface;
use Notifluxion\LaravelNotify\Contracts\DriverInterface;

class SyncQueueStrategy implements QueueStrategyInterface
{
    /**
     * Push a notification immediately in sync mode.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     * @param DriverInterface $driver
     * @param \DateTimeInterface|null $scheduleAt
     * @return mixed
     */
    public function push($notifiable, $notification, DriverInterface $driver, ?\DateTimeInterface $scheduleAt = null)
    {
        return $driver->send($notifiable, $notification);
    }

    /**
     * No-op for sync queue strategy, as it's processed instantly.
     */
    public function process(): void
    {
        // No-op
    }
}
