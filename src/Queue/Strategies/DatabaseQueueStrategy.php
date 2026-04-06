<?php

namespace Notifluxion\LaravelNotify\Queue\Strategies;

use Notifluxion\LaravelNotify\Contracts\QueueStrategyInterface;
use Notifluxion\LaravelNotify\Contracts\DriverInterface;
use Illuminate\Support\Facades\DB;

class DatabaseQueueStrategy implements QueueStrategyInterface
{
    /**
     * Push a notification to the database for asynchronous processing.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     * @param DriverInterface $driver
     * @param \DateTimeInterface|null $scheduleAt
     * @param string|null $tag
     * @return mixed
     */
    public function push($notifiable, $notification, DriverInterface $driver, ?\DateTimeInterface $scheduleAt = null, ?string $tag = null)
    {
        $tenantId = app('config')->get('notify.tenant_resolver') 
            ? call_user_func(app('config')->get('notify.tenant_resolver')) 
            : (auth()->check() ? auth()->user()->tenant_id ?? null : null);

        $payload = [
            'tenant_id' => $tenantId,
            'notifiable_id' => $notifiable->id ?? null,
            'notifiable_type' => get_class($notifiable),
            'driver' => get_class($driver),
            // Embed the full target state natively in the serialization string in case it's a transient CLI class
            'notification' => serialize(['notifiable' => $notifiable, 'notification' => $notification]),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
            'schedule_at' => $scheduleAt,
            'tag' => $tag,
        ];

        DB::table('scheduled_notifications')->insert($payload);
        
        return true;
    }

    /**
     * Cancel pending jobs grouped by a specific tag.
     *
     * @param string $tag
     * @return int
     */
    public function cancelByTag(string $tag): int
    {
        return DB::table('scheduled_notifications')->where('tag', $tag)->delete();
    }

    /**
     * Process pending notifications.
     */
    public function process(): void
    {
        // Simple lock mechanism using Laravel query builder
        $notifications = DB::table('scheduled_notifications')
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('schedule_at')
                      ->orWhere('schedule_at', '<=', now());
            })
            ->orderBy('id', 'asc')
            ->limit(10)
            ->lockForUpdate()
            ->get();

        foreach ($notifications as $notification) {
            DB::table('scheduled_notifications')
                ->where('id', $notification->id)
                ->update(['status' => 'processing', 'updated_at' => now()]);

            try {
                $notifiableClass = $notification->notifiable_type;
                $notifiableId = $notification->notifiable_id;

                // Decode notification wrapper array
                $wrapper = unserialize($notification->notification);
                $notificationObj = is_array($wrapper) ? $wrapper['notification'] : $wrapper;

                if ($notifiableId && method_exists($notifiableClass, 'find')) {
                    $notifiable = $notifiableClass::find($notifiableId);
                } else {
                    // Recover transient CLI object state directly from the wrapper
                    $notifiable = (is_array($wrapper) && isset($wrapper['notifiable'])) ? $wrapper['notifiable'] : (class_exists($notifiableClass) ? new $notifiableClass : null);
                }
                
                $driverClass = $notification->driver;
                $configBase = app('config')->get('notify') ?? [];
                
                // Dynamically unpack exact driver config to prevent credentials missing errors 
                if (str_contains($driverClass, 'SmtpDriver')) $driverConfig = $configBase['channels']['email']['smtp'] ?? [];
                elseif (str_contains($driverClass, 'SendGridDriver')) $driverConfig = $configBase['channels']['email']['sendgrid'] ?? [];
                elseif (str_contains($driverClass, 'TwilioDriver')) $driverConfig = $configBase['channels']['sms']['twilio'] ?? [];
                elseif (str_contains($driverClass, 'WhatsappApiDriver')) $driverConfig = $configBase['channels']['whatsapp']['api'] ?? [];
                else $driverConfig = [];

                $driver = new $driverClass($driverConfig);
                
                if ($notificationObj instanceof \Notifluxion\LaravelNotify\Contracts\RehydratesState) {
                    if (!$notificationObj->rehydrate($notifiable)) {
                        DB::table('scheduled_notifications')
                            ->where('id', $notification->id)
                            ->update(['status' => 'cancelled', 'updated_at' => now()]);
                        continue;
                    }
                }

                // Execute actual notification logic securely!
                $driver->send($notifiable, $notificationObj);

                DB::table('scheduled_notifications')
                    ->where('id', $notification->id)
                    ->update(['status' => 'completed', 'updated_at' => now()]);
                    
                event(new \Notifluxion\LaravelNotify\Events\NotificationSent($notifiable, $notificationObj, $channel ?? 'email'));
            } catch (\Exception $e) {
                $attempts = ($notification->attempts ?? 0) + 1;
                $configBase = app('config')->get('notify') ?? [];
                $maxRetries = $configBase['queue']['max_retries'] ?? 3;
                
                if ($attempts < $maxRetries) {
                    $delay = $configBase['queue']['retry_delay'] ?? 5;
                    DB::table('scheduled_notifications')
                        ->where('id', $notification->id)
                        ->update([
                            'status' => 'pending', 
                            'attempts' => $attempts,
                            'schedule_at' => now()->addMinutes($delay),
                            'updated_at' => now()
                        ]);
                } else {
                    // Maximum retries hit. Attempt Fallback driver routing.
                    $channel = 'custom';
                    $driverClass = $notification->driver;
                    if (str_contains($driverClass, 'Smtp') || str_contains($driverClass, 'SendGrid')) $channel = 'email';
                    elseif (str_contains($driverClass, 'Twilio')) $channel = 'sms';
                    elseif (str_contains($driverClass, 'Whatsapp')) $channel = 'whatsapp';

                    $fallbacks = $configBase['fallbacks'][$channel] ?? [];
                    if (!empty($fallbacks)) {
                        $fallbackDriverName = $fallbacks[0]; // Push to highest priority failover
                        $fallbackClass = null;
                        
                        if ($fallbackDriverName === 'smtp') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver::class;
                        elseif ($fallbackDriverName === 'sendgrid') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Email\SendGridDriver::class;
                        elseif ($fallbackDriverName === 'twilio') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Sms\TwilioDriver::class;
                        elseif ($fallbackDriverName === 'api') $fallbackClass = \Notifluxion\LaravelNotify\Drivers\Whatsapp\WhatsappApiDriver::class;

                        // Only queue fallback if it's legally different from the crash victim
                        if ($fallbackClass && $fallbackClass !== $driverClass) {
                            DB::table('scheduled_notifications')->insert([
                                'notifiable_id' => $notification->notifiable_id,
                                'notifiable_type' => $notification->notifiable_type,
                                'driver' => $fallbackClass,
                                'notification' => $notification->notification,
                                'status' => 'pending',
                                'schedule_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    // Mark original payload cluster as terminally failed 
                    DB::table('scheduled_notifications')
                        ->where('id', $notification->id)
                        ->update(['status' => 'failed', 'attempts' => $attempts, 'updated_at' => now()]);
                        
                    event(new \Notifluxion\LaravelNotify\Events\NotificationFailed($notifiable ?? null, $notificationObj ?? null, $channel ?? 'email', $e));
                }
            }
        }
    }
}
