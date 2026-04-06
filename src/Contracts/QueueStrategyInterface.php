<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface QueueStrategyInterface
{
    /**
     * Push a notification onto the queue/strategy.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     * @param DriverInterface $driver
     * @param \DateTimeInterface|null $scheduleAt
     * @param string|null $tag
     * @return mixed
     */
    public function push($notifiable, $notification, DriverInterface $driver, ?\DateTimeInterface $scheduleAt = null, ?string $tag = null);

    /**
     * Cancel pending jobs grouped by a specific tag.
     *
     * @param string $tag
     * @return int The number of cancelled messages
     */
    public function cancelByTag(string $tag): int;

    /**
     * Process/Consume from the strategy (useful for database polling or sync flush).
     *
     * @return void
     */
    public function process(): void;
}
