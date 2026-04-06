<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface DriverInterface
{
    /**
     * Send a notification using this driver.
     *
     * @param mixed $notifiable The recipient of the notification.
     * @param mixed $notification The notification payload.
     * @return mixed The response from the provider, or a true/false status.
     * 
     * @throws \Exception
     */
    public function send($notifiable, $notification);
}
