<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface RehydratesState
{
    /**
     * Refresh the database state of the notifiable and the notification.
     * Return false to abort sending the notification.
     *
     * @param mixed $notifiable
     * @return bool
     */
    public function rehydrate($notifiable): bool;
}
