<?php

namespace Notifluxion\LaravelNotify\Notifications;

class GenericNotification
{
    public $data;
    public $tag;

    public function __construct(array $data, string $tag)
    {
        $this->data = $data;
        $this->tag = $tag;
    }

    public function via($notifiable)
    {
        return [$this->tag];
    }

    public function toMail($notifiable)
    {
        return $this->data;
    }

    public function toTwilio($notifiable)
    {
        return $this->data;
    }

    public function toWhatsapp($notifiable)
    {
        return $this->data;
    }
}
