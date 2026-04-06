# Laravel Notifluxion 🚀

A scalable, versioned, future-proof, and provider-agnostic Notification engine for Laravel 10/11+.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mohitbdeshmukh/notifluxion.svg?style=flat-square)](https://packagist.org/packages/mohitbdeshmukh/notifluxion)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x_|_11.x-FF2D20.svg?style=flat-square)](https://laravel.com)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

---

## 🎯 Features

- **Multi-Channel Dispatching:** Built-in decoupled drivers for SMTP, SendGrid, Twilio SMS, and WhatsApp Cloud APIs avoiding massive vendor SDK bloat.
- **Queue Agnostic Engine:** Natively ships with independent `Sync`, `Database`, and high-speed `Redis` sub-job queue boundaries.
- **Failover / Fallback Routing:** Intelligent exception catchers that automatically increment backoff timers or silently bounce dead payloads to backup drivers on the fly!
- **Zero API N+1 Loops:** Sending an array of 5,000+ targeted user endpoints triggers a raw sub-batching bulk SQL insert natively avoiding database crashes.
- **Multi-Tenant Logging:** Built entirely with organizational `tenant_id` scopes out of the box dynamically isolated.

<br>

## 🔌 Quick Install

Install securely via Composer natively:

```bash
composer require mohitbdeshmukh/notifluxion
```

Execute the built-in scaffolding daemon to boot the config file and migrate the advanced multi-tenant background tables across to your database:

```bash
php artisan notify:install
```

Configure your `.env` variables safely inside your main Laravel application natively:
```dotenv
# Core Routings
NOTIFY_EMAIL_DRIVER=smtp
NOTIFY_SMS_DRIVER=twilio
NOTIFY_WHATSAPP_DRIVER=api
NOTIFY_EMAIL_FALLBACK=sendgrid

# Queue Scale Metrics
NOTIFY_QUEUE_ENABLED=true
NOTIFY_QUEUE_STRATEGY=database
NOTIFY_QUEUE_RETRIES=3
NOTIFY_QUEUE_DELAY_MINS=5

# API Bindings
NOTIFY_SENDGRID_KEY=SG.your_token
TWILIO_SID=AC...
TWILIO_TOKEN=your_token
TWILIO_FROM=+10000000000
WHATSAPP_TOKEN=your_meta_token
WHATSAPP_PHONE_ID=your_meta_phone_id
```

## 🚀 Multi-Channel Usage Maps

The architecture uses a unified strategy. You provide a payload array, and Notifluxion routes it dynamically based on the channel attributes inside your `$notifiable` user object.

### 1. Email Channel (SendGrid / SMTP)
The email driver automatically intercepts arrays of addresses or singular objects containing an `email` property. It natively renders generic keys or compiles actual `.blade.php` files dynamically!

```php
use Notifluxion\LaravelNotify\Facades\Notify;
use Notifluxion\LaravelNotify\Notifications\GenericNotification;

$users = ['mohit@example.com', 'admin@example.com'];

Notify::send($users, [
    'subject' => 'System Scales Flawlessly!',
    'cc' => ['team@example.com'],
    'bcc' => ['compliance@example.com'],
    'view' => 'emails.marketing', 
    'viewData' => ['name' => 'Mohit']
]);
```

### 2. SMS Channel (Twilio)
To trigger an SMS dispatch, your target object must contain a `phone_number` property and you specify the `"sms"` channel tag.

```php
$user = new \stdClass();
$user->phone_number = '+14155552671';

// Trigger Twilio Pipeline natively
$sms = new GenericNotification(['message' => 'Your OTP is 98213!'], 'sms');
Notify::send($user, $sms);
```

### 3. WhatsApp Channel (Meta API)
To hit the Meta WhatsApp Cloud API seamlessly, ensure your target object uses `whatsapp_number`, and inject your exact Meta Template name.

```php
$customer = new \stdClass();
$customer->whatsapp_number = '14155552671';

// Trigger WhatsApp Cloud Pipeline
$whatsapp = new GenericNotification([
    'template' => 'hello_world',
    'language' => 'en_US'
], 'whatsapp');

Notify::send($customer, $whatsapp);
```

### 4. Custom Channels (Slack, Push, Telegram)
Because Notifluxion natively extends Laravel's strict `Manager` class, it securely supports infinite custom drivers out of the box *without* touching the core library! 

Simply implement the `DriverInterface` and bind it into your host App's `AppServiceProvider`:

```php
use Notifluxion\LaravelNotify\Facades\Notify;
use App\Drivers\TelegramDriver; // Your custom class

public function boot()
{
    Notify::extend('telegram', function ($app) {
        return new TelegramDriver($app['config']['services.telegram']);
    });
}
```
Now, you can just set `NOTIFY_DEFAULT_SMS=telegram` in your `.env` and Notifluxion will instantly boot your custom driver and supply it with all the Native fallback, batching, and Queue architectural loops automatically!

### 5. Background Queues & Batching
To orchestrate intelligent Background daemons (auto-retries, delay backoffs, and fallback drivers enforced natively!), simply turn on `NOTIFY_QUEUE_ENABLED=true` and run:
```bash
php artisan notify:work
```

### 6. Omnichannel Broadcasting
Notifluxion effortlessly routes a singular Notification class across multiple channels concurrently. Simply return a target array natively from your `via()` methods!

```php
// Inside your Notification class
public function via($notifiable) {
    return tap([], function(&$channels) use ($notifiable) {
        if ($notifiable->email) $channels[] = 'email';
        if ($notifiable->phone_number) $channels[] = 'sms';
    });
}
```
*Note: Using this concurrently triggers the `Sub-Job Batching Engine`. A 5,000 user blast routed to both Email and SMS compiles seamlessly into a strictly optimized SQL bulk-insert!*

### 7. Advanced Reminder Engine (Scheduling & Cascades)
You do not need to boot complex Laravel Schedulers or cron expressions to handle delayed sequences. Notifluxion natively supports reverse/forward interval cascades pushed instantly across drivers.

```php
use Notifluxion\LaravelNotify\Scheduling\ScheduleBuilder;

// Trigger notifications precisely 24 hours, 1 hour, and 15 mins before a meeting!
$schedule = (new ScheduleBuilder())->before($meeting->start_date, ['24h', '1h', '15m']);

Notify::send($user, new MeetingReminder(), $schedule);
```

**Cancellable Tags Support**: Because Redis delayed jobs naturally block standard queues, any Notification returning a native `notificationTag()` interface can be halted globally securely!

```php
Notify::cancelTag("invoice_1234_reminders");
```

## 🧪 Testing
The library enforces strict test coverage and behavioral assertions. We natively support dual testing loops.

**1. Automated Unit Suite**
To execute the isolated PHPUnit behavioral tests:
```bash
vendor/bin/phpunit
```

**2. Live Driver Sandbox**
To execute the live architectural sandbox and verify your `.env` API credentials are actively broadcasting properly, run the included daemon:

*(If installed inside a regular Laravel application):*
```bash
php artisan notify:test-live
```

*(If developing the package locally):*
```bash
vendor/bin/testbench notify:test-live
```

*Note: All core use-cases and driver tests are continually logged. Verify the Test Case Registry for detailed scenario outputs.*

---

## 🔒 License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
