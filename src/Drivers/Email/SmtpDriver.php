<?php

namespace Notifluxion\LaravelNotify\Drivers\Email;

use Notifluxion\LaravelNotify\Contracts\EmailDriverInterface;

class SmtpDriver implements EmailDriverInterface
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
        $payload = is_array($notification) ? $notification : $notification->toMail($notifiable);

        $emailValue = $notifiable->email ?? (is_string($notifiable) ? $notifiable : null);
        $emails = is_array($emailValue) ? $emailValue : (isset($payload['to']) && is_array($payload['to']) ? $payload['to'] : [$emailValue]);
        $emails = array_filter($emails);

        if (empty($emails)) {
            throw new \Exception('Notifiable entity has no email address attached.');
        }

        $subject = $payload['subject'] ?? 'Notification';
        
        $message = '';
        if (isset($payload['view'])) {
            $viewData = $payload['viewData'] ?? [];
            $message = clone \Illuminate\Support\Facades\View::make($payload['view'], $viewData)->render();
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

        $mailCallback = function ($mail) use ($emails, $subject, $cc, $bcc) {
            $mail->to($emails)->subject($subject);
            if (!empty($cc)) $mail->cc($cc);
            if (!empty($bcc)) $mail->bcc($bcc);
            if (isset($this->fromAddress)) {
                $mail->from($this->fromAddress, $this->fromName ?? null);
            }
        };

        if ($isHtml) {
            \Illuminate\Support\Facades\Mail::html($message, $mailCallback);
        } else {
            \Illuminate\Support\Facades\Mail::raw($message, $mailCallback);
        }

        return true;
    }
}
