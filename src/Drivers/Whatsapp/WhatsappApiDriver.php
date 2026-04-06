<?php

namespace Notifluxion\LaravelNotify\Drivers\Whatsapp;

use Notifluxion\LaravelNotify\Contracts\WhatsappDriverInterface;

class WhatsappApiDriver implements WhatsappDriverInterface
{
    protected array $config;
    protected string $templateName;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function template(string $template): self
    {
        $this->templateName = $template;
        return $this;
    }

    public function send($notifiable, $notification)
    {
        $phone = $notifiable->whatsapp_number ?? (is_string($notifiable) ? $notifiable : null);
        if (!$phone) return false;

        $token = $this->config['token'] ?? null;
        $phoneId = $this->config['phone_number_id'] ?? null;
        
        if (!$token || !$phoneId) throw new \Exception('WhatsApp API credentials missing.');

        $payload = is_array($notification) ? $notification : (method_exists($notification, 'toWhatsapp') ? $notification->toWhatsapp($notifiable) : []);
        $template = $this->templateName ?? ($payload['template'] ?? 'hello_world');

        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->post("https://graph.facebook.com/v17.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => 'en_US']
                ]
            ]);

        if ($response->failed()) {
            throw new \Exception('WhatsApp API Error: ' . $response->body());
        }

        return $response->json();
    }
}
