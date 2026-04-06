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
     * @return mixed
     */
    public function push($notifiable, $notification, DriverInterface $driver, ?\DateTimeInterface $scheduleAt = null);

    /**
     * Process/Consume from the strategy (useful for database polling or sync flush).
     *
     * @return void
     */
    public function process(): void;
}
