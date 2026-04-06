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
     * @param string|null $tag
     * @return mixed
     */
    public function push($notifiable, $notification, DriverInterface $driver, ?\DateTimeInterface $scheduleAt = null, ?string $tag = null)
    {
        return $driver->send($notifiable, $notification);
    }

    /**
     * Cancel pending jobs grouped by a specific tag.
     *
     * @param string $tag
     * @return int
     */
    public function cancelByTag(string $tag): int
    {
        // Sync runs immediately, nothing to cancel
        return 0;
    }

    /**
     * No-op for sync queue strategy, as it's processed instantly.
     */
    public function process(): void
    {
        // No-op
    }
}
