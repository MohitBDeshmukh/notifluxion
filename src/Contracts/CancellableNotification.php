<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface CancellableNotification
{
    /**
     * Get the unique tag to group or track this queued notification.
     * Overwrite this to provide a custom grouping logic (e.g. "invoice_1234").
     *
     * @return string
     */
    public function notificationTag(): string;
}
