<?php

namespace Notifluxion\LaravelNotify\Queue\Strategies;

use Notifluxion\LaravelNotify\Contracts\QueueStrategyInterface;
use Notifluxion\LaravelNotify\Contracts\DriverInterface;
use Illuminate\Support\Facades\Redis;

class RedisQueueStrategy implements QueueStrategyInterface
{
    protected string $queue = 'notify_queue';

    public function push($notifiable, $notification, DriverInterface $driver, ?\DateTimeInterface $scheduleAt = null)
    {
        $payload = [
            'notifiable_id' => $notifiable->id ?? null,
            'notifiable_type' => get_class($notifiable),
            'driver' => get_class($driver),
            'notification' => serialize(['notifiable' => $notifiable, 'notification' => $notification]),
            'attempts' => 0
        ];

        if ($scheduleAt) {
            Redis::zadd($this->queue . ':delayed', $scheduleAt->getTimestamp(), json_encode($payload));
        } else {
            Redis::rpush($this->queue, json_encode($payload));
        }
        
        return true;
    }

    public function process(): void
    {
        // 1. Move delayed items strictly ready for dispatch
        $now = now()->timestamp;
        $delayed = Redis::zrangebyscore($this->queue . ':delayed', '-inf', $now);
        
        if (!empty($delayed)) {
            foreach ($delayed as $item) {
                Redis::rpush($this->queue, $item);
                Redis::zrem($this->queue . ':delayed', $item);
            }
        }

        // 2. Process list up to 10 batches seamlessly
        $configBase = app('config')->get('notify') ?? [];
        $maxRetries = $configBase['queue']['max_retries'] ?? 3;

        for ($i = 0; $i < 10; $i++) {
            $payloadRaw = Redis::lpop($this->queue);
            if (!$payloadRaw) break;

            $payload = json_decode($payloadRaw, true);
            
            try {
                $notifiableClass = $payload['notifiable_type'];
                $notifiableId = $payload['notifiable_id'];

                $wrapper = unserialize($payload['notification']);
                $notificationObj = is_array($wrapper) ? $wrapper['notification'] : $wrapper;

                if ($notifiableId) {
                    $notifiable = $notifiableClass::find($notifiableId);
                } else {
                    $notifiable = (is_array($wrapper) && isset($wrapper['notifiable'])) ? $wrapper['notifiable'] : new $notifiableClass;
                }
                
                $driverClass = $payload['driver'];
                
                if (str_contains($driverClass, 'SmtpDriver')) $driverConfig = $configBase['channels']['email']['smtp'] ?? [];
                elseif (str_contains($driverClass, 'SendGridDriver')) $driverConfig = $configBase['channels']['email']['sendgrid'] ?? [];
                elseif (str_contains($driverClass, 'TwilioDriver')) $driverConfig = $configBase['channels']['sms']['twilio'] ?? [];
                elseif (str_contains($driverClass, 'WhatsappApiDriver')) $driverConfig = $configBase['channels']['whatsapp']['api'] ?? [];
                else $driverConfig = [];

                $driver = new $driverClass($driverConfig);
                $driver->send($notifiable, $notificationObj);

            } catch (\Exception $e) {
                $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;
                
                if ($payload['attempts'] < $maxRetries) {
                    $delay = $configBase['queue']['retry_delay'] ?? 5;
                    Redis::zadd($this->queue . ':delayed', now()->addMinutes($delay)->getTimestamp(), json_encode($payload));
                } else {
                    // Fallback logic
                    $channel = 'custom';
                    if (str_contains($driverClass, 'Smtp') || str_contains($driverClass, 'SendGrid')) $channel = 'email';
                    elseif (str_contains($driverClass, 'Twilio')) $channel = 'sms';
                    elseif (str_contains($driverClass, 'Whatsapp')) $channel = 'whatsapp';

                    $fallbacks = $configBase['fallbacks'][$channel] ?? [];
                    if (!empty($fallbacks)) {
                        $fallbackDriverName = $fallbacks[0];
                        $fallbackClass = null;
                        
                        if ($fallbackDriverName === 'smtp') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver::class;
                        elseif ($fallbackDriverName === 'sendgrid') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Email\SendGridDriver::class;
                        elseif ($fallbackDriverName === 'twilio') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Sms\TwilioDriver::class;
                        elseif ($fallbackDriverName === 'api') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Whatsapp\WhatsappApiDriver::class;

                        if ($fallbackClass && $fallbackClass !== $driverClass) {
                            $payload['driver'] = $fallbackClass;
                            $payload['attempts'] = 0;
                            Redis::rpush($this->queue, json_encode($payload));
                        }
                    }
                }
            }
        }
    }
}
