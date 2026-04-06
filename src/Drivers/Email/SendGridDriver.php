<?php

namespace Notifluxion\LaravelNotify\Drivers\Email;

use Notifluxion\LaravelNotify\Contracts\EmailDriverInterface;

class SendGridDriver implements EmailDriverInterface
{
    protected array $config;
    protected string $fromAddress;
    protected ?string $fromName;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function from(string $address, ?string $name = null): self
    {
        $this->fromAddress = $address;
        $this->fromName = $name;
        return $this;
    }

    public function send($notifiable, $notification)
    {
        $apiKey = $this->config['api_key'] ?? null;
        if (!$apiKey) throw new \Exception('SendGrid API key missing.');

        $payload = is_array($notification) ? $notification : (method_exists($notification, 'toMail') ? $notification->toMail($notifiable) : []);
        
        $emailValue = $notifiable->email ?? (is_string($notifiable) ? $notifiable : null);
        $emails = is_array($emailValue) ? $emailValue : (isset($payload['to']) && is_array($payload['to']) ? $payload['to'] : [$emailValue]);
        $emails = array_filter($emails);
        if (empty($emails)) return false;

        $subject = $payload['subject'] ?? 'Notification';

        $message = '';
        if (isset($payload['view'])) {
            $viewData = $payload['viewData'] ?? [];
            $message = \Illuminate\Support\Facades\View::make($payload['view'], $viewData)->render();
        } else {
            $message = $payload['message'] ?? (is_string($payload) ? $payload : json_encode($payload));
            if (isset($payload['shortcodes']) && is_array($payload['shortcodes'])) {
                foreach ($payload['shortcodes'] as $key => $value) {
                    $message = str_replace(['{{ ' . $key . ' }}', '{{' . $key . '}}'], $value, $message);
                }
            }
        }

        $cc = isset($payload['cc']) ? (is_array($payload['cc']) ? $payload['cc'] : [$payload['cc']]) : [];
        $bcc = isset($payload['bcc']) ? (is_array($payload['bcc']) ? $payload['bcc'] : [$payload['bcc']]) : [];
        $isHtml = isset($payload['view']) || str_contains($message, '<html') || str_contains($message, '<body');

        // Build SendGrid JSON schema natively
        $personalizations = [
            'to' => array_values(array_map(function($e) { return ['email' => $e]; }, $emails))
        ];
        if (!empty($cc)) $personalizations['cc'] = array_values(array_map(function($e) { return ['email' => $e]; }, $cc));
        if (!empty($bcc)) $personalizations['bcc'] = array_values(array_map(function($e) { return ['email' => $e]; }, $bcc));

        $body = [
            'personalizations' => [$personalizations],
            'from' => [
                'email' => $this->fromAddress ?? config('mail.from.address', 'hello@example.com'),
                'name' => $this->fromName ?? config('mail.from.name', 'Laravel')
            ],
            'subject' => $subject,
            'content' => [
                [
                    'type' => $isHtml ? 'text/html' : 'text/plain',
                    'value' => $message
                ]
            ]
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
            ->post('https://api.sendgrid.com/v3/mail/send', $body);

        if ($response->failed()) {
            throw new \Exception('SendGrid API Error: ' . $response->body());
        }

        return $response->json();
    }
}
