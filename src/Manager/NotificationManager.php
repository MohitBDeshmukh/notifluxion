<?php

namespace Notifluxion\LaravelNotify\Manager;

use Illuminate\Support\Manager;
use Illuminate\Contracts\Foundation\Application;
use Notifluxion\LaravelNotify\Contracts\ManagerInterface;
use Notifluxion\LaravelNotify\Contracts\DriverInterface;
use InvalidArgumentException;

class NotificationManager extends Manager implements ManagerInterface
{
    /**
     * Get a notification channel instance.
     *
     * @param string|null $name
     * @return \Notifluxion\LaravelNotify\Contracts\DriverInterface
     */
    public function channel(?string $name = null): DriverInterface
    {
        $driver = $this->driver($name);

        if (!$driver instanceof DriverInterface) {
            throw new InvalidArgumentException(
                "Driver must implement " . DriverInterface::class
            );
        }

        return $driver;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('notify.default.email') ?? 'smtp';
    }

    /**
     * Send the given payload as a notification.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     * @param \DateTimeInterface|null $scheduleAt
     * @return void
     */
    public function send($notifiables, $notification, ?\DateTimeInterface $scheduleAt = null): void
    {
        $strategy = $this->resolveQueueStrategy();
        
        $channelName = (is_object($notification) && method_exists($notification, 'via')) 
            ? ((is_iterable($notifiables) && isset($notifiables[0])) ? $notification->via($notifiables[0])[0] : $notification->via($notifiables)[0]) 
            : 'email';
        
        $driver = $this->channel($this->config->get("notify.default.{$channelName}"));

        // Sub-Job Batching Engine (N+1 Query Elimination)
        if ($strategy instanceof \Notifluxion\LaravelNotify\Queue\Strategies\DatabaseQueueStrategy && is_iterable($notifiables) && !is_string($notifiables)) {
            $inserts = [];
            $tenantId = app('config')->get('notify.tenant_resolver') 
                ? call_user_func(app('config')->get('notify.tenant_resolver')) 
                : (auth()->check() ? auth()->user()->tenant_id ?? null : null);
               
            foreach ($notifiables as $notifiable) {
                $inserts[] = [
                    'tenant_id' => $tenantId,
                    'notifiable_id' => is_object($notifiable) ? ($notifiable->id ?? null) : null,
                    'notifiable_type' => is_object($notifiable) ? get_class($notifiable) : gettype($notifiable),
                    'driver' => get_class($driver),
                    'notification' => serialize(['notifiable' => $notifiable, 'notification' => $notification]),
                    'status' => 'pending',
                    'attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'schedule_at' => $scheduleAt,
                ];
            }
            \Illuminate\Support\Facades\DB::table('scheduled_notifications')->insert($inserts);
            event(new \Notifluxion\LaravelNotify\Events\NotificationQueued($notifiables, $notification, $channelName));
            return;
        }

        $iterableNotifiables = (is_iterable($notifiables) && !is_string($notifiables)) ? $notifiables : [$notifiables];

        foreach ($iterableNotifiables as $notifiable) {
            try {
                $strategy->push($notifiable, $notification, $driver, $scheduleAt);
            
            if ($strategy instanceof \Notifluxion\LaravelNotify\Queue\Strategies\SyncQueueStrategy) {
                event(new \Notifluxion\LaravelNotify\Events\NotificationSent($notifiable, $notification, $channelName));
            } else {
                event(new \Notifluxion\LaravelNotify\Events\NotificationQueued($notifiable, $notification, $channelName));
            }
        } catch (\Exception $e) {
            // Only execute synchronous iteration if we are bypassed off database queues 
            if ($strategy instanceof \Notifluxion\LaravelNotify\Queue\Strategies\SyncQueueStrategy) {
                $fallbacks = $this->config->get("notify.fallbacks.{$channelName}", []);
                $fallbackSuccess = false;

                foreach ($fallbacks as $fallbackDriverName) {
                    try {
                        $fallbackDriver = $this->channel($fallbackDriverName);
                        $fallbackDriver->send($notifiable, $notification);
                        $fallbackSuccess = true;
                        break;
                    } catch (\Exception $fallbackException) {
                        continue; // try next failover
                    }
                }

                if ($fallbackSuccess) {
                    event(new \Notifluxion\LaravelNotify\Events\NotificationSent($notifiable, $notification, $channelName));
                } else {
                    event(new \Notifluxion\LaravelNotify\Events\NotificationFailed($notifiable, $notification, $channelName, $e));
                    throw $e; // All drivers flatlined, throw original stack
                }
            } else {
                // If it crashes during a Database push, the MySQL instance is down. We cannot save it natively.
                throw $e;
            }
        }
    }
    /**
     * Resolve the active queue strategy.
     *
     * @return \Notifluxion\LaravelNotify\Contracts\QueueStrategyInterface
     */
    protected function resolveQueueStrategy()
    {
        $enabled = $this->config->get('notify.queue.enabled', false);
        $strategyType = $enabled ? $this->config->get('notify.queue.strategy', 'database') : 'sync';

        // We bind the strategies in the service provider
        return $this->container->make("notify.strategy.{$strategyType}");
    }

    /**
     * Get the current version of the library.
     *
     * @return string
     */
    public static function version(): string
    {
        return '1.0.0';
    }

    /**
     * Create an instance of the SMTP driver.
     *
     * @return \Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver
     */
    protected function createSmtpDriver()
    {
        return new \Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver(
            $this->config->get('notify.channels.email.smtp', [])
        );
    }

    /**
     * Create an instance of the SendGrid driver.
     *
     * @return \Notifluxion\LaravelNotify\Drivers\Email\SendGridDriver
     */
    protected function createSendgridDriver()
    {
        return new \Notifluxion\LaravelNotify\Drivers\Email\SendGridDriver(
            $this->config->get('notify.channels.email.sendgrid', [])
        );
    }

    /**
     * Create an instance of the Twilio driver.
     *
     * @return \Notifluxion\LaravelNotify\Drivers\Sms\TwilioDriver
     */
    protected function createTwilioDriver()
    {
        return new \Notifluxion\LaravelNotify\Drivers\Sms\TwilioDriver(
            $this->config->get('notify.channels.sms.twilio', [])
        );
    }

    /**
     * Create an instance of the WhatsApp API driver.
     *
     * @return \Notifluxion\LaravelNotify\Drivers\Whatsapp\WhatsappApiDriver
     */
    protected function createApiDriver()
    {
        return new \Notifluxion\LaravelNotify\Drivers\Whatsapp\WhatsappApiDriver(
            $this->config->get('notify.channels.whatsapp.api', [])
        );
    }
}
