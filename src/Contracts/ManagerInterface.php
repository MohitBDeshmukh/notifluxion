<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface ManagerInterface
{
    /**
     * Get a notification channel instance.
     *
     * @param string|null $name
     * @return \Notifluxion\LaravelNotify\Contracts\DriverInterface
     */
    public function channel(?string $name = null): DriverInterface;

    /**
     * Send the given payload as a notification.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     * @param \DateTimeInterface|null $scheduleAt
     * @return void
     */
    public function send($notifiable, $notification, ?\DateTimeInterface $scheduleAt = null): void;

    /**
     * Get the current version of the library.
     *
     * @return string
     */
    public static function version(): string;
}
