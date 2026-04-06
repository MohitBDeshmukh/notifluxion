<?php

namespace Notifluxion\LaravelNotify\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notifiable;
    public $notification;
    public $channel;
    public $exception;

    public function __construct($notifiable, $notification, $channel, $exception = null)
    {
        $this->notifiable = $notifiable;
        $this->notification = $notification;
        $this->channel = $channel;
        $this->exception = $exception;
    }
}
