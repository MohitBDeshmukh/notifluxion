<?php

namespace Notifluxion\LaravelNotify\Drivers\Sms;

use Notifluxion\LaravelNotify\Contracts\SmsDriverInterface;

class TwilioDriver implements SmsDriverInterface
{
    protected array $config;
    protected string $fromNumber;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function from(string $sender): self
    {
        $this->fromNumber = $sender;
        return $this;
    }

    public function send($notifiable, $notification)
    {
        $phone = $notifiable->phone_number ?? (is_string($notifiable) ? $notifiable : null);
        if (!$phone) return false;

        $sid = $this->config['sid'] ?? null;
        $token = $this->config['token'] ?? null;
        $from = $this->fromNumber ?? ($this->config['from'] ?? null);

        if (!$sid || !$token || !$from) throw new \Exception('Twilio credentials missing.');

        $payload = is_array($notification) ? $notification : (method_exists($notification, 'toTwilio') ? $notification->toTwilio($notifiable) : []);
        $message = $payload['message'] ?? 'You have a new message.';

        $response = \Illuminate\Support\Facades\Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $phone,
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            throw new \Exception('Twilio API Error: ' . $response->body());
        }

        return $response->json();
    }
}
