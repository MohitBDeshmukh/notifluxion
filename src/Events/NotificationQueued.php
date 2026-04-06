<?php

namespace Notifluxion\LaravelNotify\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationQueued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notifiable;
    public $notification;
    public $channel;

    public function __construct($notifiable, $notification, $channel)
    {
        $this->notifiable = $notifiable;
        $this->notification = $notification;
        $this->channel = $channel;
    }
}
